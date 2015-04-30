<?php
/*------------------------------------------------------------------------
# Cobalt
# ------------------------------------------------------------------------
# @author Cobalt
# @copyright Copyright (C) 2012 cobaltcrm.org All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website: http://www.cobaltcrm.org
-------------------------------------------------------------------------*/

namespace Cobalt\Model;

use Cobalt\Helper\DealHelper;
use Cobalt\Helper\DropdownHelper;
use Joomla\Date\Date;
use Cobalt\Helper\UsersHelper;

// no direct access
defined( '_CEXEC' ) or die( 'Restricted access' );

class ImportListeNazha extends DefaultModel
{
    public function readCSVFileNazha($fichiercsv)
    {
      $result = array();
            
      $row = 1;
      if (($handle = fopen($fichiercsv, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
              if ( $row!=1 )
              { 
                $num = count($data);
                                
                $ent = new \stdClass;                       
                $ent->type_de_contact = $data[1];
                $ent->Code_com = $data[2];
                $ent->APE = $data[3];
                $ent->Activite_principale = $data[4];
                $ent->Forme_juridique = $data[5];
                $ent->SEXE = $data[6];
                $ent->Nom_dirigeant1 = $data[7];
                $ent->date_naissance_dirigeant1 = $data[8];
                $ent->nom_dirigeant2 = $data[9];
                $ent->date_naissance_dirigeant2 = $data[10];
                $ent->Contact_priviliege = $data[11];
                $ent->Denomination = $data[12];
                $ent->Enseigne_nom_commercial = $data[13];
                $ent->Coord_X = $data[14];
                $ent->Coord_Y = $data[15];
                $ent->Adresse = $data[16];
                $ent->Siege = $data[17];
                $ent->CP = $data[18];
                $ent->Ville = $data[19];
                $ent->Zone_dactivite = $data[20]; 
                $ent->SIRET = $data[21]; 
                $ent->SIREN = $data[22];  
                $ent->telephone = $data[23];
                $ent->Portable = $data[24];
                $ent->Telecopie = $data[25];
                $ent->email = $data[26];
                $ent->site_web = $data[27];
                $ent->Date_creation = $data[28];
                $ent->Effectif = $data[31];
                
                $result[] = $ent;                
                                       
                /*for ($c=0; $c < $num; $c++) {
                    $ent = new stdClass();
                    
                    echo '<pre>';
                    var_dump($data[$c]) . "<br />\n";
                    echo '</pre>';
                }*/
              }
              $row++;          
          }
          fclose($handle);
      }
            
      return $result;        
    }
 
    public function prepareDataToImport($data)
    {
      $db = $this->getDb();
      
      $query = "SELECT cust_cf.* FROM #__company_custom_cf AS cust_cf INNER JOIN #__companies AS comp ON comp.id=cust_cf.company_id WHERE cust_cf.custom_field_id=1";
      $db->setQuery($query);
      
      try
      {            
        // If it fails, it will throw a RuntimeException
        $list = $db->loadObjectList(); 
      }
      catch (RuntimeException $e)
      {
        echo 'Exception '.$e->getMessage();
        
        return false;
      }
            
      // On préparre deux array avec les siret du fichier à importer et un autre avec ceux dans la table #__companies
      $list_siret_table = array();
      
      foreach($list as $item)
      {
        $list_siret_table[$item->company_id] = $item->value;
      }
      
      $list_siret_file = array();
      
      foreach($data['creation'] as $item)
      {
        $list_siret_file[] = $item->siret;        
      }      
      
      // On compare les siret présents dans la table #__companies avec ceux du fichier que l'on veut importer
      $res_compare = array_diff($list_siret_file, $list_siret_table);
      
      if( !empty($res_compare))
      {
        $this->injectNewCompaniesInsee($data['creation'], $res_compare);
      }
      
      if ( !empty($data['mise_a_jour']) )
      {
        // Sélectionner toutes les entreprises en faisant une jointure pour obtenir le siret et/ou le siren de chaque entreprise
        
        foreach($data['mise_a_jour'] as $ent)
        {
          $query = '';
        }
      }      
    }
    
    public function injectNewCompaniesNazha($datafile)
    {
      $db = $this->getDb();
      
      $date = new Date;      
              
      foreach($datafile as $file)
      {
        $query = $db->getQuery(true);
         
        // Insert columns.
        $columns = array('owner_id' ,'name', 'description', 'address_1', 'address_2', 'address_city', 'address_zip', 'created', 'phone', 'modified', 'fax', 'email');
         
        // Insert values.
        $values = array(UsersHelper::getUserId(), $db->quote($file->Denomination), $db->quote($file->Enseigne_nom_commercial), $db->quote($file->Adresse), $db->quote($file->Zone_dactivite),$db->quote($file->Ville),$db->quote($file->CP),$db->quote($date->format($db->getDateFormat())),$db->quote($file->telephone),$db->quote($date->format($db->getDateFormat())),$db->quote($file->Telecopie),$db->quote($file->email) );
         
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__companies'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);
        
        try
        { 
          $db->execute();
        }
        catch (RuntimeException $e)
        {
          echo 'Exception '.$e->getMessage();
          
          return false;
        }
        
        //echo $db->insertid();        
      }         
       
      
      /*$company_id = '37';
            
      $values_to_insert_cf_siret = array();
      $values_to_insert_cf_siren = array();
      $values_to_insert_cf_act_prin = array();
      $values_to_insert_cf_form_jur = array();
      $values_to_insert_cf_effectif = array();
      $values_to_insert_cf_an_creation = array();
      $values_to_insert_cf_code_ape = array();
      $values_to_insert_cf_siege = array();
      $values_to_insert_cf_lib_ape = array();
      $values_to_insert_cf_surface_commerce = array();
      $values_to_insert_cf_nature_etab_entre_indi = array();
      $values_to_insert_cf_origine_creation_etab = array();
      $values_to_insert_cf_participation_part_prod_etab = array();
      $values_to_insert_cf_categorie_entreprise = array();
      
      foreach($datafile as $file)
      {
        $values_to_insert_cf_siret[] = '(' . $company_id . ',1,' . $db->quote($file->SIRET) . ','. $db->quote($date->format($db->getDateFormat())) .')';
        $values_to_insert_cf_siren[] = '(' . $company_id . ',3,' . $db->quote($file->SIREN) . ','. $db->quote($date->format($db->getDateFormat())) .')';
        $values_to_insert_cf_act_prin[] = '(' . $company_id . ',4,' . $db->quote($file->Activite_principale) . ','. $db->quote($date->format($db->getDateFormat())) .')';
        $values_to_insert_cf_form_jur[] = '(' . $company_id . ',5,' . $db->quote($file->Forme_juridique) . ','. $db->quote($date->format($db->getDateFormat())) .')';
        $values_to_insert_cf_an_creation[] = '(' . $company_id . ',15,' . $db->quote($file->Date_creation) . ','. $db->quote($date->format($db->getDateFormat())) .')';
        $values_to_insert_cf_effectif[] = '(' . $company_id . ',17,' . $db->quote($file->Effectif) . ','. $db->quote($date->format($db->getDateFormat())) .')';
        $values_to_insert_cf_code_com[] = '(' . $company_id . ',18,' . $db->quote($file->Code_com) . ','. $db->quote($date->format($db->getDateFormat())) .')';               
      }
      
      $values_cf = implode(',',$values_to_insert_cf_siret);
      $values_cf .= ','.implode(',',$values_to_insert_cf_siren); 
      $values_cf .= ','.implode(',',$values_to_insert_cf_act_prin); 
      $values_cf .= ','.implode(',',$values_to_insert_cf_form_jur);
      $values_cf .= ','.implode(',',$values_to_insert_cf_an_creation);
      $values_cf .= ','.implode(',',$values_to_insert_cf_effectif); 
      $values_cf .= ','.implode(',',$values_to_insert_cf_code_com);                    
      
      // On injecte les éléments dans la table #__company_custom_cf
      $query = 'INSERT INTO #__company_custom_cf ("company_id", "custom_field_id", "value", "modified") VALUES '.$values_cf;
      $db->setQuery($query);
            
      try
      {            
        // If it fails, it will throw a RuntimeException
        //$db->execute(); 
      }
      catch (RuntimeException $e)
      {
        echo 'Exception '.$e->getMessage();
        
        return false;
      } */
    }
        
    /**
     * Import a CSV File
     * @param  [String]  $data
     * @param  [String]  $model [ Model to import ]
     * @return [Boolean] $success
     */
    public function importCSVInseeData($data)
    {
      $db = $this->getDb();
      
      // On met à jour ou injecte les données dans la base de données
      if ( !empty($data['creation']) )
      {
        $date = new Date;
        
        $values_to_insert = array();
        foreach($data['creation'] as $ent)
        {
          $values_to_insert[] = '(' . UsersHelper::getUserId() . ',' . $db->quote($ent->l1_nomen) . ',' . $db->quote($ent->l2_comp) . ',' . $db->quote($ent->l4_voie) . ',' . $db->quote($ent->l5_disp) . ',' . $db->quote($ent->libcom) . ',' . $db->quote($ent->codpos) . ','. $db->quote($date->format($db->getDateFormat())) .','. $db->quote($date->format($db->getDateFormat())) .')';  
        }
        
        $values = implode(',',$values_to_insert);
        
        $query = 'INSERT INTO #__companies ("owner_id" ,"name", "description", "address_1", "address_2", "address_city", "address_zip", "created", "modified") VALUES '.$values;        
        $db->setQuery($query);
            
        try
        {            
          // If it fails, it will throw a RuntimeException
          $db->query(); 
        }
        catch (RuntimeException $e)
        {
          echo 'Exception '.$e->getMessage();
        
          return false;
        }
      }
      
      if ( !empty($data['mise_a_jour']) )
      {
        // Sélectionner toutes les entreprises en faisant une jointure pour obtenir le siret et/ou le siren de chaque entreprise
        
        foreach($data['mise_a_jour'] as $ent)
        {
          $query = '';
        }
      }    
    }
    
}

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

class ImportCCI extends DefaultModel
{
    /**
     * Transform the CSV file given into object
     */         
    public function readCSVFileCCI($fichiercsv)
    {
      $entreprises_creation = array();
      $entreprises_mise_a_jour = array();
      $result = array();
      
      $tabCar = array(" ", "\t", "\n", "\r", "\0", "\x0B", "\xA0");
      
      $listTitre = array('Mme', 'M.', 'Mlle');
      
      $result = array();
      
      $row = 1;
      if (($handle = fopen($fichiercsv, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
              if ( $row!=1 )
              { 
                $num = count($data);
                                
                $ent = new \stdClass;                       
                $ent->raison_sociale = $data[0];
                $ent->enseigne_nom_commercial = $data[1];
                $ent->siret = str_replace($tabCar, array(), $data[2]);
                $ent->siren = substr($ent->siret,0,8);
                $ent->forme_juridique_libelle = $data[3];
                $ent->statut_libelle = $data[4];
                $ent->capital = $data[5];
                $ent->date_creation = $data[6];
                $ent->origine_du_fond = $data[7];
                $ent->code_ape = $data[8];
                $ent->libelle_ape = $data[9];
                $ent->activite_etablissement = $data[10];
                $ent->adresse_voie = $data[11];
                $ent->adresse_complement = $data[12];
                $ent->adresse_boite_postale = $data[13];
                $ent->adresse_code_postal = $data[14];
                $ent->adresse_commune = $data[15];
                $ent->telephone = $data[16];
                $ent->telecopie = $data[17];
                $ent->website = $data[18];
                $ent->tranche_effectif = $data[19];
                $ent->date_cessation_entreprise = !empty($data[20]) ? $data[20] :'';
                $ent->motif_cessation = $data[21];
                $ent->date_cessation_etab = !empty($data[22]) ? $data[22] : '';
                $ent->motif_cessation_etab = $data[23];
                $ent->creancier = $data[24];
                $ent->repertoire_metier = $data[25];
                $ent->dirigeants1 = trim(str_replace($listTitre, array(), $data[26]));
                $ent->type_dirigeants1 = $data[27];
                $ent->dirigeants2 = trim(str_replace($listTitre, array(), $data[28]));
                $ent->type_dirigeants2 = $data[29];
                $ent->dirigeants3 = trim(str_replace($listTitre, array(), $data[30]));
                $ent->type_dirigeants3 = !empty($data[31]) ? $data[31] : '';
                
                $result[] = $ent;
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
    
    public function codeINSEECommune($nom_commune)
    {
      if ($nom_commune=='FAVERGES') {
        $code_comm = '74123';
      } elseif ($nom_commune=='DOUSSARD') {
        $code_comm = '74104';
      } elseif ($nom_commune=='LATHUILE') {
        $code_comm = '74147';
      } elseif ($nom_commune=='SEYTHENEX') {
        $code_comm = '74270';
      } elseif ($nom_commune=='MONTMIN') {
        $code_comm = '74187';
      } elseif ($nom_commune=='ST FERREOL') {
        $code_comm = '74234';
      } elseif ($nom_commune=='CONS STE COLOMBE') {
        $code_comm = '74084';
      } elseif ($nom_commune=='CHEVALINE') {
        $code_comm = '74072';
      } elseif ($nom_commune=='GIEZ') {
        $code_comm = '74135';
      } elseif ($nom_commune=='MARLENS') {
        $code_comm = '74167';
      }
      
      return $code_comm;
    } 
    
    public function statusEntreprise($date_cessation_entreprise, $date_cessation_etab)
    {
      return 1;
      if ( !empty($date_cessation_entreprise) )
      {
        return '-1';
      }
      elseif( !empty($date_cessation_etab) )
      {
        return '-1';
      }
    }
    
    public function injectNewCompaniesCCI($datafile)
    {
      $db = $this->getDb();
      
      $date = new Date;
      
      foreach($datafile as $line)
      {
        $query = $db->getQuery(true);
        
        $published = $this->statusEntreprise($line->date_cessation_entreprise, $line->date_cessation_etab);
         
        // Insert columns.
        $columns = array('owner_id' ,'name', 'description', 'address_1', 'address_2', 'address_city', 'address_zip', 'created', 'phone', 'modified', 'fax', 'website', 'published');
         
        // Insert values.
        $values = array(UsersHelper::getUserId(), $db->quote(utf8_encode($line->raison_sociale)), $db->quote(utf8_encode($line->enseigne_nom_commercial)), $db->quote(utf8_encode($line->adresse_voie)), $db->quote($line->adresse_complement), $db->quote($line->adresse_commune), $db->quote($line->adresse_code_postal), $db->quote($date->format($db->getDateFormat())), $db->quote($line->telephone), $db->quote($date->format($db->getDateFormat())), $db->quote($line->telecopie), $db->quote($line->website), $db->quote($published));
         
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__companies'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));        
        $db->setQuery($query);
                        
        try
        {        
          // If it fails, it will throw a RuntimeException
          $db->execute();
        }
        catch (RuntimeException $e)
        {
          echo 'Exception '.$e->getMessage();
          
          return false;
        }
        
        $company_id = $db->insertid();
        
        $query = 'INSERT INTO #__company_custom_cf (company_id, custom_field_id, value, modified) VALUES
         (' . $company_id . ',1,' . $db->quote($line->siret) . ','. $db->quote($date->format($db->getDateFormat())) .'),
         (' . $company_id . ',3,' . $db->quote($line->siren) . ','. $db->quote($date->format($db->getDateFormat())) .'),
         (' . $company_id . ',4,' . $db->quote(utf8_encode($line->ativite_etablissement)) . ','. $db->quote($date->format($db->getDateFormat())) .'),
         (' . $company_id . ',5,' . $db->quote($line->forme_juridique_libelle) . ','. $db->quote($date->format($db->getDateFormat())) .'),
         (' . $company_id . ',15,' . $db->quote($line->date_creation) . ','. $db->quote($date->format($db->getDateFormat())) .'),
         (' . $company_id . ',16,' . $db->quote($line->date_suppression) . ','. $db->quote($date->format($db->getDateFormat())) .'),
         (' . $company_id . ',17,' . $db->quote($line->tranche_effectif) . ','. $db->quote($date->format($db->getDateFormat())) .'),
         (' . $company_id . ',18,' . $db->quote($this->codeINSEECommune($line->adresse_commune)) . ','. $db->quote($date->format($db->getDateFormat())) .'),
         (' . $company_id . ',19,' . $db->quote($line->code_ape) . ','. $db->quote($date->format($db->getDateFormat())) .')';       
        $db->setQuery($query);
        
        echo $query;
              
        try
        {            
          // If it fails, it will throw a RuntimeException
          $db->execute(); 
        }
        catch (RuntimeException $e)
        {
          echo 'Exception '.$e->getMessage();
          
          return false;
        }
        
        // On importe aussi les noms des dirigeants correspondants aux entreprises
        if ( !empty($line->dirigeants1) && !empty($line->dirigeants2) && !empty($line->dirigeants3) )
        {
          $last_first_name1 = explode(' ',$line->dirigeants1);
          $last_first_name2 = explode(' ',$line->dirigeants2);
          $last_first_name3 = explode(' ',$line->dirigeants3); 
                 
          $query = 'INSERT INTO #__people (owner_id, first_name, last_name, company_id, position, source_id, created, status_id, modified) VALUES
          (313, ' . $db->quote($last_first_name1[0]) . ' , ' . $db->quote($last_first_name1[1]) . ' , '. $company_id .',' .$db->quote($line->type_dirigeants1). ', 6,'. $db->quote($date->format($db->getDateFormat())) .', 10 ,'. $db->quote($date->format($db->getDateFormat())) .'),
          (313, ' . $db->quote($last_first_name2[0]) . ' , ' . $db->quote($last_first_name2[1]) . ' , '. $company_id .',' .$db->quote($line->type_dirigeants2). ', 6,'. $db->quote($date->format($db->getDateFormat())) .','. $db->quote($date->format($db->getDateFormat())) .'),
          (313, ' . $db->quote($last_first_name3[0]) . ' , ' . $db->quote($last_first_name3[1]) . ' , '. $company_id .','  .$db->quote($line->type_dirigeants3). ', 6,'. $db->quote($date->format($db->getDateFormat())) .','. $db->quote($date->format($db->getDateFormat())) .')';
        }
        else if ( !empty($line->dirigeants1) && !empty($line->dirigeants2) )
        {
          $last_first_name1 = explode(' ',$line->dirigeants1);
          $last_first_name2 = explode(' ',$line->dirigeants2);
          
          $query = 'INSERT INTO #__people (owner_id, first_name, last_name, company_id, position, source_id, created, status_id, modified) VALUES
          (313, ' . $db->quote($last_first_name1[0]) . ' , ' . $db->quote($last_first_name1[1]) . ' , '. $company_id .',' .$db->quote($line->type_dirigeants1). ', 6,'. $db->quote($date->format($db->getDateFormat())) .', 10 ,'. $db->quote($date->format($db->getDateFormat())) .'),
          (313, ' . $db->quote($last_first_name2[0]) . ' , ' . $db->quote($last_first_name2[1]) . ' , '. $company_id .',' .$db->quote($line->type_dirigeants2). ', 6,'. $db->quote($date->format($db->getDateFormat())) .', 10 ,'. $db->quote($date->format($db->getDateFormat())) .')';
        }
        else if ( !empty($line->dirigeants1) )
        {
          $last_first_name1 = explode(' ',$line->dirigeants1);
          
          $query = 'INSERT INTO #__people (owner_id, first_name, last_name, company_id, position, source_id, created, status_id, modified) VALUES
          (313, ' . $db->quote($last_first_name1[0]) . ' , ' . $db->quote($last_first_name1[1]) . ' , '. $company_id .',' .$db->quote($line->type_dirigeants1). ', 6,'. $db->quote($date->format($db->getDateFormat())) .', 10 ,'. $db->quote($date->format($db->getDateFormat())) .')';
        }

        $db->setQuery($query);
              
        try
        {            
          // If it fails, it will throw a RuntimeException
          $db->execute(); 
        }
        catch (RuntimeException $e)
        {
          echo 'Exception '.$e->getMessage();
          
          return false;
        } 
      }      
    }
        
    /**
     * Import a CSV File
     * @param  [String]  $data
     * @param  [String]  $model [ Model to import ]
     * @return [Boolean] $success
     */
    public function importCSVCCIData($data)
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

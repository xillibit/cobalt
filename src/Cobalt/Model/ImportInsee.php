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

class ImportInsee extends DefaultModel
{
    /**
     * Transform the CSV file given into object
     */         
    public function readCSVFileINSEE($fichiercsv)
    {
      $entreprises_creation = array();
      $entreprises_mise_a_jour = array();
      $result = array();
      
      $row = 1;
      if (($handle = fopen($fichiercsv, "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
              if ( $row!=1 )
              { 
                $num = count($data);
                                
                $ent = new \stdClass;                       
                $ent->siren = $data[0];
                $ent->nic = $data[1];
                // On crée le SIRET car il n'existe pas
                if ( strlen($data[1])== 2 )
                {
                  $ent->siret = $data[0].'000'.$data[1];
                }
                else
                {
                  $ent->siret = $data[0].'00'.$data[1];
                }
                            
                $ent->l1_nomen = utf8_encode($data[2]);
                $ent->l2_comp = $data[3];
                $ent->l3_cadr = $data[4];
                $ent->l4_voie = $data[5];
                $ent->l5_disp = $data[6];
                $ent->l6_post = $data[7];
                $ent->l7_etrg = $data[8];
                $ent->rpet = $data[9];
                $ent->depet = $data[10];
                $ent->arronet = $data[11];
                $ent->ctonet = $data[12];
                $ent->comet = $data[13];
                $ent->libcom = $data[14];
                $ent->du = $data[15];
                $ent->tu = $data[16];
                $ent->uu = $data[17];
                $ent->epci = $data[18];
                $ent->tcd = $data[19];
                $ent->zemet = $data[20];
                $ent->codevoie = $data[21];
                $ent->numvoie = $data[22];
                $ent->indrep = $data[23];
                $ent->typvoie = $data[24];
                $ent->libvoie = $data[25];
                $ent->codpos = $data[26];
                $ent->cdex = $data[27];
                $ent->zr1 = $data[28];
                $ent->siege = $data[29];
                $ent->enseigne = $data[30];
                $ent->nom_com =$data[31];
                $ent->natetab = $data[32];
                $ent->libnatetab =  utf8_encode($data[33]);
                $ent->apet700 = $data[34];
                $ent->libapet = utf8_encode($data[35]);
                $ent->dapet = $data[36];
                $ent->tefet = $data[37];
                $ent->efetcent = $data[38];
                $ent->defet = $data[39];
                $ent->origine = $data[40];
                $ent->dcret = $data[41];
                $ent->amintret = $data[42];
                $ent->activnat = $data[43];
                $ent->lieuact = $data[44];
                $ent->actisurf = $data[45];
                $ent->saisonat = $data[46];
                $ent->modet = $data[47];
                $ent->prodet = $data[48];
                $ent->prodpart = $data[49];
                $ent->auxilt = $data[50];
                $ent->zr2 = $data[51];
                $ent->nomen_long = $data[52];
                $ent->sigle = $data[53];
                $ent->civilite = $data[54];
                $ent->nj = $data[55];
                $ent->libnj = utf8_encode($data[56]);
                $ent->apen700 = $data[57];
                $ent->libapen = utf8_encode($data[58]);
                $ent->dapen = $data[59];
                $ent->aprm = $data[60];
                $ent->tefen = $data[61];
                $ent->efencent = $data[62];
                $ent->defen = $data[63];
                $ent->categorie = $data[64];
                $ent->dcren = $data[65];
                $ent->amintren = $data[66];
                $ent->monoact = $data[67];
                $ent->moden = $data[68];
                $ent->proden = $data[69];
                $ent->esaan = $data[70];
                $ent->tca = $data[71];
                $ent->esaapen = $data[72];
                $ent->esasec1n = $data[73];
                $ent->esasec1n = $data[74];
                $ent->esasec3n = $data[75];
                $ent->esasec4n = $data[76];
                $ent->regimp = $data[77];
                $ent->monoreg = $data[78];
                $ent->zr3 = $data[79];
                $ent->rpen = $data[80];
                $ent->depcomen = $data[81];
                $ent->vmaj = $data[82];
                $ent->vmaj1 = $data[83];
                $ent->vmaj2 = $data[84];
                $ent->vmaj3 = $data[85];
                $ent->ind_public = $data[86];            
                
                if ( $ent->vmaj =='C' )
                {
                  // On crée un tableau avec les éléments à créer
                  $entreprises_creation[] = $ent;
                }
                elseif ( $ent->vmaj == 'I' || $ent->vmaj == 'F'  )
                {
                  // On crée un autre tableau avec les éléments à mettre à jour
                  $entreprises_mise_a_jour[] = $ent;
                }
                                       
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
      
      $result['creation'] = $entreprises_creation;
      $result['mise_a_jour'] = $entreprises_mise_a_jour;
      
      return $result;        
    }
 
    public function getHeaders($data)
    {
      $headers = array();
            
      foreach($data['creation'][0] as $key=>$value) {
        $headers[] = $key;
      }
      
      return $headers;
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
                              
      var_dump($list_siret_file);
      
      // On compare les siret présents dans la table #__companies avec ceux du fichier que l'on veut importer
      $res_compare = array_diff($list_siret_file, $list_siret_table);
      
      foreach($list_siret_file)
      {
      
      }
      
      /*f( !empty($res_compare))
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
      } */     
    }
    
    public function injectNewCompaniesInsee($data)
    {
      $db = $this->getDb();
      
      $date = new Date;
        
      foreach($data as $item)
      {
        $query = $db->getQuery(true);
        
        // Insert values.
        $values = array(UsersHelper::getUserId(),$db->quote($item->l1_nomen),$db->quote($item->l2_comp),$db->quote($item->l4_voie),$db->quote($item->l5_disp),$db->quote($item->libcom),$db->quote($item->codpos),$db->quote($date->format($db->getDateFormat())),$db->quote($date->format($db->getDateFormat())));
      
        // Insert columns.
        $columns = array('owner_id' ,'name', 'description', 'address_1', 'address_2', 'address_city', 'address_zip', 'created', 'modified');
         
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
        
        $query = $db->getQuery(true);
        
        $columns = array('company_id', 'custom_field_id', 'value', 'modified');
                
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__company_custom_cf'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', array($company_id,1,$db->quote($item->siret),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,3,$db->quote($item->siren),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,4,$db->quote($item->apet700),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,5,$db->quote($item->nj),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,15,$db->quote($item->dcren),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,17,$db->quote($item->efetcent),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,18,$db->quote($item->depcomen),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,19,$db->quote($item->apen700),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,20,$db->quote($item->rpen),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,22,$db->quote($item->libapen),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,23,$db->quote($item->actisurf),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,24,$db->quote($item->natetab),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,27,$db->quote($item->prodpart),$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',', array($company_id,28,$db->quote($item->categorie),$db->quote($date->format($db->getDateFormat())))));                    
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
        
        /* Le fichier INSEE ne dipose pas des noms des dirigeants
        
        $query = $db->getQuery(true);
        
        $columns = array('owner_id' ,'first_name', 'last_name', 'company_id', 'source_id', 'position', 'created', 'status_id', 'modified');
        
        echo 'dirigeant1 '.$item->Nom_dirigeant1.' dirigeant2 '.$item->nom_dirigeant2;
        
        if ( !empty($item->Nom_dirigeant1) && !empty($item->nom_dirigeant2) )
        {
          $last_first_name1 = explode(' ',$item->Nom_dirigeant1);
          $last_first_name2 = explode(' ',$item->nom_dirigeant2); 
          
          $source_id = 0;
          if ( !empty($item->type_de_contact) ) $source_id = getCobaltTypeContact($line->type_de_contact);
          else $source_id = 2;
          
          $query
            ->insert($db->quoteName('#__people'))
            ->columns($db->quoteName($columns))
            ->values(implode(',',array(397, $db->quote($last_first_name1[0]), $db->quote($last_first_name1[1]) , $company_id, $source_id, $db->quote('Dirigeant'), $db->quote($date->format($db->getDateFormat())), 10 ,$db->quote($date->format($db->getDateFormat())))))
            ->values(implode(',',array(397, $db->quote($last_first_name2[0]) , $db->quote($last_first_name2[1]) , $company_id, $source_id, $db->quote('Dirigeant'), $db->quote($date->format($db->getDateFormat())), 10 ,$db->quote($date->format($db->getDateFormat())))));
                 
        }
        else if ( !empty($item->Nom_dirigeant1) )
        {
          $last_first_name1 = explode(' ',$item->Nom_dirigeant1);
          
          $query
            ->insert($db->quoteName('#__people'))
            ->columns($db->quoteName($columns))
            ->values(implode(',',array(397, $db->quote($last_first_name1[0]), $db->quote($last_first_name1[1]), $company_id, $source_id, $db->quote('Dirigeant'), $db->quote($date->format($db->getDateFormat())), 10 , $db->quote($date->format($db->getDateFormat())))));
        }
        else if ( !empty($item->nom_dirigeant2) )
        {
          $last_first_name2 = explode(' ',$item->nom_dirigeant2);
          
          $query
            ->insert($db->quoteName('#__people'))
            ->columns($db->quoteName($columns))
            ->values(implode(',',array(397, $db->quote($last_first_name2[0]) , $db->quote($last_first_name2[1]) , $company_id, $source_id, $db->quote('Dirigeant'), $db->quote($date->format($db->getDateFormat())), 10 ,$db->quote($date->format($db->getDateFormat())))));
          
       }

        echo ' requete '.$query.' ';
        

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
        } */
      }
      
    }
    
    public function updateWithInseeData($data)
    {
      // On boucle dans chaque ligne du fichier CSV, pour chaque ligne on charge les données dans #__companies et #__company_custom_cf
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

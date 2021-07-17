<?php 

namespace Drupal\ngt_general;

use Drupal\file\Entity\File;
use Drupal\rest\ResourceResponse;
use Drupal\user\Entity\User;
use Drupal\media\Entity\Media;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;

class methodGeneral{
   
   
    /**
     * @param string $fid
     *   File id.
     */
    public function setFileAsPermanent($fid) {
        \Drupal::service('page_cache_kill_switch')->trigger();
        if (is_array($fid)) {
            $fid = array_shift($fid);
        }

        $file = File::load($fid);
        if (!is_object($file)) {
            return;
        }

        $file->setPermanent();
        $file->save();
        \Drupal::service('file.usage')->add($file, 'ngt', 'ngt', $fid);
    }

        
    /**
     * renderLogo
     *
     * @return void
     */
    public function renderLogo(){
        \Drupal::service('page_cache_kill_switch')->trigger();
        // build uri logo
        $logo = [
            'image_src_general_settings_logo' => '',
            'image_src_img_second_logo' => '',
            'activated_second_logo' => false,
        ];

        $image_src_general_settings_logo = '';
        $image_src_img_second_logo = '';

        $conf = \Drupal::config('ngt_general.settings');
        $img_general_settings_logo = $conf->get('general_settings')['img_logo'];
        $img_second_logo = $conf->get('general_settings')['img_logo_second'];
        $activate_img_logo_second = $conf->get('general_settings')['activate_img_logo_second'];

        $logo['activated_second_logo'] = $activate_img_logo_second == true ? true : false;
        
        if ( is_array($img_general_settings_logo) ) {
            $fid = reset($img_general_settings_logo);  
            $file = File::load($fid);
            isset($file) ? $logo['general_settings_logo'] = $file->getFileUri() : '';
        }

        if ( is_array($img_second_logo) ) {
            $fid = reset($img_second_logo);  
            $file = File::load($fid);
            isset($file) ? $logo['second_logo'] = $file->getFileUri() : '';
        }
        
        return $logo;
    }
    
    /**
     * loadTermByCategory
     *
     * @param  string $name
     * @return array
     */
    public function loadTermByCategory($name, $parent = 0){
        
        $term = [];
        
        $query = \Drupal::entityQuery('taxonomy_term');
        $query->condition('vid', $name);
        $tids = $query->execute();

        if($tids){
            $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);
            foreach ($terms as $value) {
                if($value->get('parent')->getValue()[0]['target_id'] == $parent){
                    array_push($term,[
                        'tid' => $value->tid->value,
                        'name' => $value->name->value
                    ]);
                }
            }
        }
        
        return $term;
    }

      /**
     * load_image
     *
     * @param  int $media_field
     * @return url
     */
    public function load_image($media_field, $style = NULL){
        $file = File::load($media_field);
        $url = $file->getFileUri();
        if ($style != NULL){
            $url = ImageStyle::load($style)->buildUrl($url);
        }
        return $url;
    }

    /**
     * load_url_file
     *
     * @param  int $media_field
     * @return string url
     */
    public function load_url_file($media_field){
        $file = File::load($media_field);
        $url = file_create_url($file->getFileUri());
        return $url;
    }
    
    /**
     * in_array_r
     *
     * @param  mixed $needle
     * @param  mixed $haystack
     * @param  mixed $strict
     * @return void
     */
    public function in_array_r($needle, $haystack, $strict = false) {
        $haystackArray = $haystack;
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
                return true;
            }
        }
        return false;
    }

}

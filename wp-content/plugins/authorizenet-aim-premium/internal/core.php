<?php
if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}

class Indatos_Authorizenetpro_Core
{

   protected $plugin_slug;
   protected $plugin_name;
   protected $update_link;
   protected $current_version;

   public function __construct() 
   {   
      $this->plugin_slug  = 'authorizenet-aim-premium';
      $this->plugin_name = 'authorizenet-aim-premium/authorizenet-aim-premium.php';
      $this->update_link   = 'https://indatos.com/updates/authorize-net-pro/updates.php';
      $this->current_version = AUTHNET_PRO_VERSION;
      add_filter('plugins_api', array(&$this, 'authnet_pro_plugin_info'), 20, 3);
      add_filter('site_transient_update_plugins', array(&$this, 'authnet_pro_push_update'), 20, 3);
      add_action( 'upgrader_process_complete', array(&$this, 'authnet_pro_after_update'), 10, 2 );
      add_action( 'in_plugin_update_message-authorizenet-aim-premium/authorizenet-aim-premium.php', array(&$this,'authnet_pro_update_message'), 10, 2 );
      add_filter( 'plugin_action_links_'.$this->plugin_name, array(&$this,'authaim_action_links') );
   }


   function authnet_pro_plugin_info( $res, $action, $args )
   {

      if( $action !== 'plugin_information' )
         return false;
      if( $this->plugin_slug !== $args->slug )
         return $res;

      if( false == $remote = get_transient( 'plugin_upgrade_authorizenet-aim-premium' ) ) {
         $wc_plugin_info = get_option('woocommerce_authorizeaim_settings');

         $remote = wp_remote_get( 
            add_query_arg( array(
               'license' => urlencode( $wc_plugin_info['license'] ),
               'site' => urlencode( home_url() ),
               'version' => urldecode( $this->current_version ),
            ), $this->update_link),
            array(
               'timeout' => 10,
               'headers' => array(
                  'Accept' => 'application/json'
               ) ) );
         if ( !is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && !empty( $remote['body'] ) ) {
            set_transient( 'plugin_upgrade_authorizenet-aim-premium', $remote, 86400 ); 
         }
      }

      if( !is_wp_error( $remote ) ) {
         $remote = json_decode( $remote['body'] );
         $res = new stdClass();
         $res->name = $remote->name;
         $res->slug = $this->plugin_slug;
         $res->version = $remote->version;
         $res->tested = $remote->tested;
         $res->requires = $remote->requires;
         $res->author = '<a href="https://indatos.com">Indatos Technologies</a>'; //
         $res->author_profile = 'https://indatos.com'; // 
         $res->download_link = $remote->download_url;
         $res->trunk = $remote->download_url;
         $res->last_updated = $remote->last_updated;
         $res->sections = array(
            'description' => $remote->sections->description, // 
         );

         if( !empty( $remote->sections->screenshots ) ) {
            $res->sections['screenshots'] = $remote->sections->screenshots;
         }
         return $res;
      }
      return false;
   }


   function authnet_pro_push_update( $transient )
   {
      if ( empty($transient->checked ) ) {
         return $transient;
      }
      if( false == $remote = get_transient( 'plugin_upgrade_authorizenet-aim-premium' ) ) {
         $wc_plugin_info = get_option('woocommerce_authorizeaim_settings');
         $remote = wp_remote_get( 
            add_query_arg( array(
               'license' => urlencode( $wc_plugin_info['license'] ),
               'site' => urlencode( home_url() ),
               'version' => urldecode( $this->current_version ),
            ), $this->update_link), array(
               'timeout' => 10,
               'headers' => array(
                  'Accept' => 'application/json'
               ) )
         );

         if ( !is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && !empty( $remote['body'] ) ) {
            set_transient( 'plugin_upgrade_authorizenet-aim-premium', $remote, 86400 ); 
         }
      }

      if( $remote && !is_wp_error( $remote ) ) {
         $remote = json_decode( $remote['body'] );
         if( $remote && version_compare( $this->current_version, $remote->version, '<' ) && version_compare($remote->requires, get_bloginfo('version'), '<=' ) ) {
            $res = new stdClass();
            $res->slug = $this->plugin_slug;
            $res->plugin = $this->plugin_name; 
            $res->new_version = $remote->version;
            $res->tested = $remote->tested;
            $res->package = $remote->download_url;
            $res->license_info = $remote->license_info;
            $transient->response[$res->plugin] = $res;
            //$transient->checked[$res->plugin] = $remote->version;
         }
      }
      return $transient;
   }


   function authnet_pro_update_message( $plugin_info_array, $plugin_info_object ) {
      if( empty( $plugin_info_array['package'] ) ) {
         if( $plugin_info_array['license_info'] == 'no_license'){
            echo 'Please add license key to update plugin. You can change/add license key in '.'<a href="admin.php?page=wc-settings&tab=checkout&section=authorizeaim" >Plugin Settings</a>';
         }elseif( $plugin_info_array['license_info'] == 'invalid_expired'){
            echo 'License Expired. Please <a href="'.$plugin_info_array['PluginURI'].'">renew your license</a> to update plugin.  You can change/add license key in '.'<a href="admin.php?page=wc-settings&tab=checkout&section=authorizeaim" >Plugin Settings</a>';
         }else{
            echo 'Please add license key to update plugin. You can change/add license key in '.'<a href="admin.php?page=wc-settings&tab=checkout&section=authorizeaim" >Plugin Settings</a>';
         }
      }
   }

   function authnet_pro_after_update( $upgrader_object, $options ) {
      if ( $options['action'] == 'update' && $options['type'] === 'plugin' )  {
         // just clean the cache when new plugin version is installed
         delete_transient( 'plugin_upgrade_authorizenet-aim-premium' );
      }
   }
   
   function authaim_action_links ( $links ) {
      $authpro_links = array(
         '<a href="admin.php?page=wc-settings&tab=checkout&section=authorizeaim" >Settings</a>',
         '<a href="http://www.indatos.com/wordpress-support/?ref=aimplugin" target="_blank">Documentation</a>',
         '<a href="http://www.indatos.com/wordpress-support/?ref=aimplugin" target="_blank">Support</a>',

      );
      return array_merge( $links, $authpro_links );
   }




}
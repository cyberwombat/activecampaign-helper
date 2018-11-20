<?php
if (!defined('ABSPATH')) {
    exit;
}

if( !class_exists( 'WP_Http' ) )
    include_once( ABSPATH . WPINC. '/class-http.php' );


/**
 * AC_Helper_API class.
 *
 * Provides AC API
 *
 */
class AC_Helper_API
{ 

    /**
     *Initialize
     */
    public function init()
    {
        add_action('ach_subscribe', array($this,'ach_subscribe'), 10, 4);;
    }

    /**
     * Susbcribe to a list
     *
     * Use `ach_subscribe` action to subscribe an email
     *
     * @param string $email - email to subscribe
     * @param array $fields - extra contact fields
     * @return
     */
    public function subscribe_to_list($email, $fields = array())
    {
        $list = get_option('ac_helper_list_id');
        
        if(!$list || !is_numeric($list)) {
          AC_Helper::log('Subscription failed - No valid AC list ID provided');
          return array('success' => false, 'error' => 'No valid AC list ID provided');
        }


        $params = array(
          'email' => $email,
          'p['.$list.']'  => $list,
          'status['.$list.']]'  => 1
        );
        $res = $this->do_api_call('contact_sync', array_merge($params, $fields));
        AC_Helper::log($email . ' subscribed to list '.$list .' - '.($res->success ? 'success!' : 'failed!'));
       
        return $res;
    }


    /**
     * Perform an AC API call
     *
     * @param string $action API action
     * @param array $params API post params
     * @return object API response
     */
    protected function do_api_call($action, $post = array())
    {
        $url = $action === 'track_event_add' 
          ? "https://trackcmp.net/event?"  
          : get_option('ac_helper_api_url') . '/admin/api.php?';

        $key = get_option('ac_helper_api_key');
        
        $params = array(
          'api_key'      => $key,
          'api_action'   => $action,
          'api_output'   => 'json'
        );
        $api = $url . build_query($params);


        $request = new WP_Http;
        $result = $request->request( $api, array( 'method' => 'POST', 'body' => $post) );

       

        if($result instanceof WP_Error) {
          return array('success' => false, 'error' => json_encode($result->errors));
        }

         $body = json_decode($result['body']);


        // Totally diff response for events then others... geez
        return (object)array( 'success' =>  isset($body->success) ? $body->success : $body->result_code, 'message' => isset($body->message) ? $body->message : '', 'response' => $body  );
    }

    /**
     * Fetch tracking id from AC
     *
     * @return string
     */
    public function fetch_track_id()
    {
        $res = $this->do_api_call('user_me');
        AC_Helper::log('Tracking ID fetched: '.$res->response->trackid.'- '.($res->success ? 'success!' : 'failed!'));
        return (string)$res->response->trackid;
    }

    /**
     * Send event
     *
     * @param string $name - event name
     * @param array $data - event data
     * @param string $email - optional email
     * @return boolean success
     */
    public function send_event($name, $value, $email)
    {        
        $res = $this->do_api_call('track_event_add', array(
          'key' => get_option('ac_helper_event_key'),   
          'actid' => get_option('ac_helper_track_id'),
          'event' => $name, 
          'eventdata' => $value,
          'visit' => array('email' => $email)
        ));

        AC_Helper::log('Event '.$name.' sent '.($email ? 'with email '.$email : 'without an email').' - '.($res->success ? 'success!' : 'failed!'));
        return $res;
    }
}



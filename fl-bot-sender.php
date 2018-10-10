<?php

#
#
# FL.RU SENDER BOT
#
# (c) Doc-sayme
#
# For the full license information, view the LICENSE file that was distributed
# with this source code.
#
#

class fl_bot_sender 
{   
    const version = '0.1';

    # "LOGIN" for autorisation in fl.ru
        public $login = '';

    # "PASSWORD" for autorisation in fl.ru
        public $password = '';

    # URL-adress to form login
        private $url_loginform = 'https://www.fl.ru/login/';
    
    # URL-adress get captcha
        private $url_captcha = 'https://www.fl.ru/xajax/captcha.server.php';

    # URL-adress projects
        private $url_projects = 'https://www.fl.ru/projects/';

    # Cookies autorisation data
        public $session_cookies = '';

    # Cookies filter orders
        private $cookies_filter = '';

    # Token key
        private $token_key = '';

    # Order categories
        public $categoryes = array();

    # Filters for orders
        public $pf_pro_only = TRUE;          # Orders for all
        public $pf_less_offers = FALSE;      # Orders have Less than 2 responses
        public $hide_exec = TRUE;            # Orders without a contractors
        public $ps_text = '';                # Key words through space
        public $pf_my_specs = TRUE;          # Orders only on your specialization 
        public $order_category = array();    # Categories 

	/*
	***************************************
    * Autorisation
    ***************************************
    */
    public function auth () {

        if( !$this ->token_key )
            $this ->get_token_key();   

        $postdata = array( 
            'login' => $this->login,  
            'passwd' => $this->password,  
            'autologin' => '0',
            'u_token_key' => $this->token_key,
        );  

        $output = $this ->curl (
            $this->url_loginform,
            $postdata,
            1,
            $this->url_loginform,
            'POST'
        );

        if( !$output )
            return FALSE;

        preg_match_all(
            '/^Set-Cookie:\s*([^;]*)/mi',
            $output,
            $arr
        );

        $this ->session_cookies .= $arr[1][0].';';

        if( !$this ->categoryes )
           $this  ->get_category();

        return $this ->session_cookies;

    }

    /*
    ***************************************
    * get_token_key 
    ***************************************
    */
    private function get_token_key () {
        
        $output = $this ->curl( 
            $this->url_loginform,
            NULL,
            TRUE
        );

        preg_match_all(
            '#_TOKEN_KEY = \'(.+?)\';#', 
            $output,
            $arr
        );

        $this->token_key = $arr[1][0];

        preg_match_all(
            '/^Set-Cookie:\s*([^;]*)/mi',
            $output,
            $arr
        );

        $this ->session_cookies = $arr[1][0].';';
        
        return TRUE;

    }

    /*
	***************************************
    * Send comment
    ***************************************
    */
    public function send_comment ( $url, $cost_from, $cost_type, $time_from, $time_type, $descr ) {

        preg_match_all(
            '#/projects/(.+?)/#',
            $url,
            $arr
        );

        if( !isset( $arr[1][0] ) )
            return FALSE;

        $pid = $arr[1][0];

        $output = $this ->curl(
            'https://www.fl.ru'.$url
        );

        preg_match_all(
            '#name="hash" value="(.+?)"#',
            $output,
            $arr
        );

        if( !isset( $arr[1][0] ) )
            return FALSE;

        $hash = $arr[1][0];
                
        $postdata = array( 
            'cost_from' => $cost_from,
            'cost_type' => $cost_type,
            'time_from' => $time_from,
            'time_type' => $time_type,
            'descr' => $descr,  
            'pid' => $pid,  
            'hash' => $hash,
            'u_token_key' => $this ->token_key,
       );

        $output = $this ->curl(
            'https://www.fl.ru'.$url,
            $postdata,
            TRUE,
            'https://www.fl.ru'.$url,
            'POST'
        );

        return $output ? TRUE : FALSE;

    }

    /*
    ***************************************
    * Get category for filters
    ***************************************
    */
    public function get_category () {
        
        $output = $this ->curl(
            $this ->url_projects
        );
        
        preg_match_all(
            '#filter_specs\[(.+?)\]#',
            $output, 
            $arr
        );

        foreach ($arr[1] as $v0) {
            
            preg_match_all(
                '#filter_specs\['.$v0.'\]=\[(.+?)\];#',
                $output, 
                $a1
            ); 
            
            preg_match_all(
                '#\[(.+?)\]#',
                $a1[1][0], 
                $a2
            ); 

            foreach ( $a2[1] as $v1 ) {
                preg_match_all(
                    '#^(.+?),#',
                    $v1, 
                    $n
                ); 
                preg_match_all(
                    '#.*,\'(.+?)\'#',
                    $v1, 
                    $t
                ); 
                
                $arr_sub_res[] = array(
                    'id' => $n[1][0],
                    'name' => $t[1][0]
                );
            }
        }
        
        $this ->categoryes = $arr_sub_res;
    }
    /*
	***************************************
    * Get url-adreses for send comment
    * $a = TRUE or FALSE; - scan full pages/scan first page. 
    * If you nessesary scan new orders, you can set FALSE;
    ***************************************
    */
    public function get_url_order ( $a=FALSE ) {

        //set filter
            $this ->set_filter();

        $output = $this ->curl(
            $this ->url_projects
        );

        preg_match_all(
            '#<a.*class="b-post__link".*href="(.+?)">#',
            $output, 
            $url_arr
        );

        $url_arr = $url_arr[1];

        if( $a ) {
            
            preg_match_all(
                '#<a class="b-pager__link" href="(.+?)">#', 
                $output, 
                $res_page_url
            );

            if( $res_page_url[1] )
                foreach ( $res_page_url[1] as $value ) {
                    $output = $this ->curl(
                        $this->url_projects.$value,
                        NULL,
                        TRUE,
                        $this ->url_projects
                    );

                    preg_match_all(
                        '#<a.*class="b-post__link".*href="(.+?)">#', 
                        $output, 
                        $urls
                    );

                    foreach ( $urls[1] as $value ) {
                        $url_arr[] = $value;
                    }
                }
        }

        return $url_arr ? $url_arr : FALSE;

    }

    /*
	***************************************
    * Set filters to reception
    ***************************************
    */
    public function set_filter () {

        $postdata = array( 
            'action' => "postfilter",
            'kind' => 5,
            'pf_pro_only' => $this ->pf_pro_only,
            'pf_less_offers' => $this ->pf_less_offers,
            'hide_exec' => $this ->hide_exec,
            'ps_text' => $this ->ps_text,  
            'pf_my_specs' => $this ->pf_my_specs,
            'u_token_key' => $this ->token_key,
       );

        //set categories
        if( $this ->order_category ){
            foreach ($this ->order_category as $value) {
               $postdata['pf_categofy[1]['.$value.']'] = 1;
            }
        }

        $output = $this ->curl (
            $this ->url_projects,
            $postdata,
            TRUE,
            $this ->url_projects,
            'POST'
        );

        preg_match_all(
            '/^Set-Cookie:\s*([^;]*)/mi', 
            $output, 
            $arr
        );

        if ( !$arr )
            return FALSE;

        foreach ( $arr[1] as $value ) {
            $cookies = isset( $cookies ) ?  $cookies.$value.';' : $value.';';
        }
        
        $this ->cookies_filter = $cookies;

        return TRUE;

    }

    /*
    ***************************************
    * Curl
    ***************************************
    */
    private function curl ( $url, $postdata=NULL, $header=NULL, $url_referer='', $method=NULL, $json=NULL ) {
        
        if( !$method )
            $method = 'GET';

        // CURL initialization

            $ch = curl_init();  
            curl_setopt( 
                $ch, 
                CURLOPT_URL, $url 
            );  
            curl_setopt( 
                $ch, 
                CURLOPT_RETURNTRANSFER, 
                1 
            ); 

        // If json
            if ( $json )
                curl_setopt( 
                    $ch, 
                    CURLOPT_HTTPHEADER, 
                    array(                                                            
                        'X-Request: JSON',
                        'X-Requested-With: XMLHttpRequest',
                    )
                ); 

            curl_setopt( 
                $ch, 
                CURLOPT_USERAGENT, 
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36'
            );

            if( $url_referer )
                curl_setopt( 
                    $ch, 
                    CURLOPT_REFERER, 
                    $url_referer 
                );
            
            curl_setopt( 
                $ch, 
                CURLOPT_CONNECTTIMEOUT, 
                30 
            );

            curl_setopt( 
                $ch, 
                CURLOPT_POST, 
                $method == 'POST' ? 
                    1 :
                    0 
            );  
            curl_setopt( 
                $ch, 
                CURLOPT_POSTFIELDS, 
                $postdata 
            ); 

            curl_setopt( 
                $ch,
                CURLOPT_SSL_VERIFYPEER,
                FALSE 
            );

            curl_setopt( 
                $ch,
                CURLOPT_COOKIE, 
                $this ->cookies_filter ? 
                    $this ->session_cookies.$this ->cookies_filter : 
                    $this ->session_cookies
            );

            curl_setopt( 
                $ch, 
                CURLOPT_HEADER, 
                $header ? 
                    1 : 
                    0 
            );

        return curl_exec( $ch );

    }

}

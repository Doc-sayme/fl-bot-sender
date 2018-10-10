<?php

    session_start();

    include 'fl-bot-sender.php';

    $fl_bot_sender = new fl_bot_sender;
        
        //set login data
            $fl_bot_sender ->login = '+79042148714';
            $fl_bot_sender ->password = 'a58148ss';

        //set user cookies
            if( !isset( $_SESSION['fl_bot_session_cookies'] ) )
                $_SESSION['fl_bot_session_cookies'] = $fl_bot_sender ->auth();

            $fl_bot_sender ->session_cookies = $_SESSION['fl_bot_session_cookies'];
        
        //Get array filter categories
            $fl_bot_sender ->categoryes;

        if( isset( $_POST['offer'] ) ){

            //set filters
            $fl_bot_sender ->pf_pro_only = TRUE;      # Orders for all
            $fl_bot_sender ->pf_less_offers = FALSE;  # Orders have Less than 2 responses
            $fl_bot_sender ->hide_exec = TRUE;        # Orders without a contractors
            $fl_bot_sender ->ps_text = '';            # Key words through space
            $fl_bot_sender ->pf_my_specs = TRUE;      # Orders only on your specialization 
            $fl_bot_sender ->order_category = FASLE;  # Categories. example array(1,9,5) from $fl_bot_sender ->categoryes;

            //Where send comment; array();
            $url = $fl_bot_sender ->get_url_order(); // - at 1 page;  get_url_order(TRUE) - all list at all pages
            $cost_from = $_POST['cost_from'];
            $cost_type = $_POST['cost_type'];
            $time_from = $_POST['time_from'];
            $time_type = $_POST['time_type'];
            $descr = $_POST['descr'];

            var_dump( $fl_bot_sender ->send_comment( $url[2], $cost_from, $cost_type, $time_from, $time_type, $descr ) );
        }

?>

    <form method="POST" action="" enctype="application/x-www-form-urlencoded">
        <label for="cost_from">Стоимость</label>
        <input type="text" name="cost_from" id="cost_from" value="" size="100" maxlength="8">
        <select name="cost_type">
            <option value="0">USD</option>
            <option value="1">Euro</option>
            <option value="2" selected="selected">Руб</option>
        </select>
        <br/>
        <label for="time_from">Срок</label>
        <input id="time_from" type="text" name="time_from" value="" size="100" maxlength="3" class="b-combo__input-text">
        <select name="time_type">
            <option value="0">в часах</option>
            <option value="1">в днях</option>
            <option value="2">в месяцах</option>
        </select>
        <br/>
        <label for="ps_text">Текст</label>
        <textarea name="descr" id="ps_text"></textarea>
        <br/>
        <button name="offer" type="submit">Опубликовать ответ</button>
    </form>

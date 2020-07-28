<?php

$messages_buffer_size = 10;

if ( isset($_POST['content']) && isset($_POST['name']) && isset($_POST['json']) && isset($_POST['img']) )
{
    $msg = $_POST['content'];

    // Open, lock and read the message buffer file
    $buffer = fopen($_POST["json"], 'r+b');
    flock($buffer, LOCK_EX);
    $buffer_data = stream_get_contents($buffer);
    
    // Append new message to the buffer data or start with a message id of 0 if the buffer is empty
    $messages = $buffer_data ? json_decode($buffer_data, true) : array();
    $next_id = (count($messages) > 0) ? $messages[count($messages) - 1]['id'] + 1 : 0;
    $messages[] = array('id' => $next_id, 'time' => time(), 'name' => $_POST['name'], 'content' => $msg, 'img' => $_POST['img']);
    
    // Remove old messages if necessary to keep the buffer size
    if (count($messages) > $messages_buffer_size)
        $messages = array_slice($messages, count($messages) - $messages_buffer_size);
    
    // Rewrite and unlock the message file
    ftruncate($buffer, 0);
    rewind($buffer);
    fwrite($buffer, json_encode($messages));
    flock($buffer, LOCK_UN);
    fclose($buffer);
    exit();
}

?>
<!DOCTYPE html>
<!-- Code and site made by Furious -->

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="Chat">
    <meta property="og:description" content="just chat">

    <title>Chat</title>

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.5.4/dist/css/uikit.min.css" />

    <!-- UIkit JS -->
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.5.4/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.5.4/dist/js/uikit-icons.min.js"></script>

    <script src="https://dolor.ml/furiouschat/js/emoji.js"></script>

    <!-- Bots -->
    <script src="js/bots/bot.js"></script>
    <script src="js/bots/senkopoggers.js"></script>

    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

    <script>

        $(document).ready(function () {
            // Remove the "loading…" list entry
            $('ul#messages > li').remove();

            channel_public();
            checkUser();

            $('form').submit(function () {
                var form = $(this);
                var name = localStorage["user"];
                var content = form.find("input[name='content']").val();

                if (name == '' || content == '')
                    return false;

                if (name == undefined)
                    alert("Not logged in.");

                if(content.indexOf('!') !== -1)  {
                    BotParse(content, name);
                    $.post(form.attr('action'), { 'name': name, 'content': content, 'json': sessionStorage["channel"], 'img': getImage() }, function (data, status) {
                        $('<li class="pending" />').text(content).prepend('<small>' + ` <span style="font-size: 1.3em !important;color: white;">${name}</span> ` + '</small>').prepend(`<img src="${getImage()}" style="border-radius: 50%; width: 35px; margin-right: 15px; float: left; margin-top: 6px; margin-left: 5px;">`).appendTo('ul#messages');
                        $('ul#messages').scrollTop($('ul#messages').get(0).scrollHeight);
                        form.find("input[name='content']").val('').focus();
                        poll_for_new_messages();
                    });

                } else {

                    $.post(form.attr('action'), { 'name': name, 'content': content, 'json': sessionStorage["channel"], 'img': getImage() }, function (data, status) {
                        $('<li class="pending" />').text(content).prepend('<small>' + ` <span style="font-size: 1.3em !important;color: white;">${name}</span> ` + '</small>').prepend(`<img src="${getImage()}" style="border-radius: 50%; width: 35px; margin-right: 15px; float: left; margin-top: 6px; margin-left: 5px;">`).appendTo('ul#messages');
                        $('ul#messages').scrollTop($('ul#messages').get(0).scrollHeight);
                        form.find("input[name='content']").val('').focus();
                        poll_for_new_messages();
                    });
                }   
                return false;

            });

            poll_for_new_messages();
            setInterval(poll_for_new_messages, 7000);
        });

        function channel_public() {
            var dir = "public/messages.json"
            sessionStorage["channel"] = dir;
        }

    </script>
    <style type="text/css">
        html {
            margin: 0em;
            padding: 0;
            overflow: auto;
        }

        html {
            margin: 0px;
            height: 100%;
            width: 100%;
        }

        body {
            margin: 0px;
            min-height: 100%;
            width: 100%;
            background: rgb(26, 26, 26);
        }

        p.subtitle {
            margin: 0;
            padding: 0 0 0 0.125em;
            font-size: 0.77em;
            color: gray;
        }

        ul#messages {
            overflow: auto;
            height: 55vh;
            padding: 1px 3px;
            list-style: none;
            background-color: rgb(44, 44, 44);
        }

        .info {
            overflow: auto;
            padding: 1px 3px;
            background: linear-gradient(to right, #181818, #424242);
        }

        ul#messages li {
            margin: 0.85em 0;
            padding: 0;
            color: white;
        }

        ul#messages li small {
            display: block;
            font-size: 0.70em;
            color: gray;
        }

        ul#messages li.pending {
            color: #aaa;
        }

        .right-center {
            margin: auto;
        }

    </style>
</head>

<body>
    <div class="container">
    
        <br>

        <div class="left-list info">

            <div style="float: left;">
                <img id="usr_img" src="https://dolor.ml/noxy.jpg" style="border-radius: 50%; width: 50px; margin-right: 15px; float: left; margin-top: 8px; margin-left: 5px;">
                <span id="usr_name" style="font-size: 1.3em !important;color: white;"></span><br>
                <p id="usr_status" style="font-size: 0.80em !important;color: white;"></p>
            </div>

            <div style="float: right" class="right-center">
                <button uk-toggle="target: #login" id="login_btn" type="button" class="uk-button uk-button-secondary">Login in</button>

                <button onclick="logout()" id="logout_btn" class="uk-button uk-button-secondary">Logout</button>

                <button uk-toggle="target: #signup" id="signup_btn" type="button" class="uk-button uk-button-secondary">Sign Up</button>

                <button uk-toggle="target: #image" type="button" class="uk-button uk-button-secondary">New IMG</button>

                <button uk-toggle="target: #status" type="button" class="uk-button uk-button-secondary">New Status</button>
            </div>

        </div>

        <div class="info uk-flex">
            <div class="uk-flex uk-flex-left">
                <h4 style="color: white;">Servers</h4>
            </div>
            
            <div style="margin-left: 10px;">
                <div id="server">
                    <button class="uk-button uk-button-secondary" onclick="channel_public(); SetServer('public')"><span uk-icon="icon: home" class="uk-icon"></span> Public</button>
                    <button class="uk-button uk-button-secondary" uk-toggle="target: #new_server" type="button"><span uk-icon="icon: home" class="uk-icon"></span> New Server</button>
                    <button class="uk-button uk-button-secondary" uk-toggle="target: #join_server" type="button"><span uk-icon="icon: home" class="uk-icon"></span> Join Server</button>

                    <div id="usr_servers"></div>
                </div>
            </div>
        </div>

        <br>
   
        <div class="info uk-flex">
            <div class="uk-flex uk-flex-left">
                <span uk-icon="icon: hashtag; ratio: 7" style="color:white"></span>
            </div>
            
            <div style="margin-left: 10px;">
                <b><h3 style="color: white;" id="name_srv">public</h3></b>
                <p style="color: white;" id="des_srv">Chat all you want here.</p>
                <button class="uk-button" onclick="DeleteServer()" id="del_srv">Remove Server</button>
            </div>
        </div>
    
        <div class="main-chat">
            <ul id="messages">
                <li>loading…</li>
            </ul>
        </div>

        <div class="bottom-chat">
            <form action="<?= htmlentities($_SERVER['PHP_SELF'], ENT_COMPAT, 'UTF-8'); ?>" method="post" enctype='multipart/form-data'>
                    <input autocomplete="off" type="text" name="content" id="content" class="uk-input uk-button-secondary"/> <button type="submit" class="uk-button uk-button-secondary"><span uk-icon="icon: play"></span> Send</button>
                    <button uk-toggle="target: #upload" type="button" class="uk-button uk-button-secondary"><span uk-icon="icon: plus"></span> Upload</button>
                    <button uk-toggle="target: #theme" type="button" class="uk-button uk-button-secondary"><span uk-icon="icon: paint-bucket"></span> Theme</button>
                    <button type="button" class="uk-button uk-button-secondary" id="emoji-button"><span uk-icon="icon: happy"></span> Emojis</button>
            </form>
        </div>
    </div>

    <div id="login" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <button class="uk-modal-close-default" type="button" uk-close></button>
            <h2 class="uk-modal-title">Login</h2>

            <label>
                Username:
                <input type="text" id="user_login" class="uk-input" />
            </label>

            <label>
                Password:
                <input type="password" id="pass_login" class="uk-input"/>
            </label>

            <br>
            <br>

            <button onclick="login(document.getElementById('user_login').value, document.getElementById('pass_login').value)" class="uk-modal-close uk-button uk-button-default" type="button">Login In</button>
        </div>
    </div>

    <div id="signup" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <button class="uk-modal-close-default" type="button" uk-close></button>
            <h2 class="uk-modal-title">Sign Up</h2>

            <label>
                Username:
                <input type="text" id="user_su" class="uk-input" />
            </label>

            <label>
                Password:
                <input type="password" id="pass_su" class="uk-input"/>
            </label>

            <br>
            <br>

            <button onclick="signup(document.getElementById('user_su').value, document.getElementById('pass_su').value)" class="uk-modal-close uk-button uk-button-default" type="button">Sign Up</button>
        </div>
    </div>

    <div id="upload" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <button class="uk-modal-close-default" type="button" uk-close></button>
            <h2 class="uk-modal-title">Upload image</h2>
            <form method="post" action="" enctype="multipart/form-data" id="upload_form">
                <div class='preview'>
                    <img src="" id="img" width="100" height="100">
                </div>
                <div >
                    <input type="file" id="file" name="file" class="uk-button uk-button-default" />                    
                    <button id="but_upload" class="uk-modal-close uk-button uk-button-default" type="button">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <div id="theme" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <button class="uk-modal-close-default" type="button" uk-close></button>        
             <h2 class="uk-modal-title">Theme Options</h2>
                <p>
                <form onSumbit="return false;" id="form_theme">
                    <label for="back">Enter a url for your background:</label>
                    <input style="outline: 1px;" id="back" class="uk-input" name="back">
                </input>
                </p>
                <button onclick="setTheme()" class="uk-modal-close uk-button uk-button-default">Ok</button>
                <button class="uk-modal-close uk-button uk-button-default" onclick="resetTheme()">Reset</button>
            </div>
        </div>
    </div>

    <div id="image" uk-modal>
        <div class="uk-modal-dialog uk-modal-body uk-dark">
            <button class="uk-modal-close-default" type="button" uk-close></button>        
             <h2 class="uk-modal-title">Change user image</h2>
             <form method="post" action="" enctype="multipart/form-data" id="img_upload_form">
                <div class='preview'>
                    <img src="" id="u_img" width="100" height="100">
                </div>
                <div ></div>
                    <input type="file" id="img_file" name="img_file" class="uk-button uk-button-default" />                    
                    <button id="img_upload" class="uk-modal-close uk-button uk-button-default" type="button">Upload</button>
                    <button class="uk-modal-close uk-button uk-button-default" onclick="resetImg()">Reset</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div id="status" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <button class="uk-modal-close-default" type="button" uk-close></button>        
             <h2 class="uk-modal-title">Set Status</h2>
                <p>
                <form onSumbit="return false;" id="form_theme">
                    <label for="back">Enter a status:</label>
                    <input style="outline: 1px;" id="status_box" class="uk-input" name="status_box">
                </input>
                </p>
                <button onclick="setStatus(document.getElementById('status_box').value)" class="uk-modal-close uk-button uk-button-default">Ok</button>
                <button class="uk-modal-close uk-button uk-button-default" onclick="resetStatus()">Reset</button>
            </div>
        </div>
    </div>

    <div id="new_server" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <button class="uk-modal-close-default" type="button" uk-close></button>        
             <h2 class="uk-modal-title">Create new server</h2>
                <p>
                <form onSumbit="return false;" id="form_theme">
                    <label for="back">Enter the server name:</label>
                    <input style="outline: 1px;" id="srv_name" class="uk-input">
                </input>
                </p>
                <button onclick="NewServer(document.getElementById('srv_name').value)" class="uk-modal-close uk-button uk-button-default">Ok</button>
            </div>
        </div>
    </div>

    <div id="join_server" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <button class="uk-modal-close-default" type="button" uk-close></button>        
             <h2 class="uk-modal-title">Join Server</h2>
                <p>
                <form onSumbit="return false;" id="form_theme">
                    <label for="back">Enter the server ID:</label>
                    <input style="outline: 1px;" id="srv_id" class="uk-input">
                </input>
                </p>
                <button onclick="JoinServer(document.getElementById('srv_id').value)" class="uk-modal-close uk-button uk-button-default">Ok</button>
            </div>
        </div>
    </div>

    <script>    

         // Poll-function that looks for new messages
         function poll_for_new_messages () {
                $.ajax({
                    url: sessionStorage["channel"], dataType: 'json', success: function (messages, status) {
                        if (!messages)
                            return;

                        $('ul#messages > li.pending').remove();

                        var last_message_id = $('ul#messages').data('last_message_id');
                        if (last_message_id == null)
                            last_message_id = -1;

                        for (var i = 0; i < messages.length; i++) {
                            var msg = messages[i];
                            if (msg.id > last_message_id) {
                                var date = new Date(msg.time * 1000);
                                if (msg.content.includes("[img]=>")) {
                                    var url = msg.content.replace('[img]=>', '');
                                    $('<li/>').append(`<img src= ${url}>`).
                                        prepend('<small>' + ` <span style="font-size: 1.3em !important;color: white;">${msg.name}</span> ` + date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes() + '</small>').
                                        prepend(`<img src="${msg.img}" style="border-radius: 50%; width: 35px; margin-right: 15px; float: left; margin-top: 6px; margin-left: 5px;">`).
                                        appendTo('ul#messages');
                                    $('ul#messages').data('last_message_id', msg.id);
                                } else if (msg.content.includes("[spotify]=>")) {
                                    var id = msg.content.replace('[spotify]=>', '');
                                    id = id.substring(id.lastIndexOf('/') + 1);
                                    $('<li/>').append(`<iframe src="https://open.spotify.com/embed/track/${id}" width="300" height="380" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>`).
                                        prepend('<small>' + ` <span style="font-size: 1.3em !important;color: white;">${msg.name}</span> ` + date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes() + '</small>').
                                        prepend(`<img src="${msg.img}" style="border-radius: 50%; width: 35px; margin-right: 15px; float: left; margin-top: 6px; margin-left: 5px;">`).
                                        appendTo('ul#messages');
                                    $('ul#messages').data('last_message_id', msg.id);
                                } else if (msg.content.includes("[youtube]=>")) {
                                    var id = msg.content.replace('[youtube]=>', '');
                                    id = id.substring(id.lastIndexOf('/') + 1);
                                    id = id.replace('watch?v=', '');
                                    $('<li/>').append(`<iframe src="https://youtube.com/embed/${id}" width="420" height="315" allow="encrypted-media"></iframe>`).
                                        prepend('<small>' + ` <span style="font-size: 1.3em !important;color: white;">${msg.name}</span> ` + date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes() + '</small>').
                                        prepend(`<img src="${msg.img}" style="border-radius: 50%; width: 35px; margin-right: 15px; float: left; margin-top: 6px; margin-left: 5px;">`).
                                        appendTo('ul#messages');
                                    $('ul#messages').data('last_message_id', msg.id);
                                } else if (msg.content.includes("[bot<auth:hJqW8i9>]=>")) {
                                    var msg_ = msg.content.replace('[bot<auth:hJqW8i9>]=>', '');
                                    $('<li/>').append(msg_).
                                        prepend('<small>' + ` <span style="font-size: 1.3em !important;color: white;">${msg.name}</span> ` + date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes() + '</small>').
                                        prepend(`<img src="${msg.img}" style="border-radius: 50%; width: 35px; margin-right: 15px; float: left; margin-top: 6px; margin-left: 5px;">`).
                                        appendTo('ul#messages');
                                    $('ul#messages').data('last_message_id', msg.id);
                                } else if (msg.content.includes("http") && !msg.content.includes("[img]=>")) {
                                    $('<li/>').append(`<a href="${msg.content}">${msg.content}</a>`).
                                        prepend('<small>' + ` <span style="font-size: 1.3em !important;color: white;">${msg.name}</span> ` + date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes() + '</small>').
                                        prepend(`<img src="${msg.img}" style="border-radius: 50%; width: 35px; margin-right: 15px; float: left; margin-top: 6px; margin-left: 5px;">`).
                                        appendTo('ul#messages');
                                    $('ul#messages').data('last_message_id', msg.id);
                                } else if (msg.content.includes("@" + localStorage["user"])) {
                                    $('<li style="background: black" />').text(msg.content).
                                        prepend('<small>' + ` <span style="font-size: 1.3em !important;color: white;">${msg.name}</span> ` + date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes() + '</small>').
                                        prepend(`<img src="${msg.img}" style="border-radius: 50%; width: 35px; margin-right: 15px; float: left; margin-top: 6px; margin-left: 5px;">`).
                                        appendTo('ul#messages');
                                    $('ul#messages').data('last_message_id', msg.id);
                                } else {
                                    $('<li/>').text(msg.content).
                                        prepend('<small>' + ` <span style="font-size: 1.3em !important;color: white;">${msg.name}</span> ` + date.getHours() + ':' + (date.getMinutes() < 10 ? '0' : '') + date.getMinutes() + '</small>').
                                        prepend(`<img onclick="showUser('${msg.name}', '${msg.img}')" src="${msg.img}" style="border-radius: 50%; width: 35px; margin-right: 15px; float: left; margin-top: 6px; margin-left: 5px;">`).
                                        appendTo('ul#messages');
                                    $('ul#messages').data('last_message_id', msg.id);
                                }
                            }
                        }

                        // Remove all but the last 50 messages in the list to prevent browser slowdown with extremely large lists
                        // and finally scroll down to the newest message.
                        $('ul#messages > li').slice(0, -50).remove();
                        $('ul#messages').scrollTop($('ul#messages').get(0).scrollHeight);
                    }
                });
            }

            function showUser(name, img) {
                UIkit.modal.alert(` <img src="${img}" style="border-radius: 50%; width: 50px; margin-right: 15px; float: left; margin-top: 8px; margin-left: 5px;"> <span style="font-size: 1.3em !important;">${name}</span><br><p style="font-size: 0.80em !important;">${getStatusUser(name)}</p>`)
            }

            function getStatusUser(user) {
                let json = $.ajax({
                    url: `api/users/${user}.json`,
                    dataType: "json",
                    async:false
                }).responseText;

                return JSON.parse(json)["status"]["current"];
            }

        const button = document.querySelector('#emoji-button');
        const picker = new EmojiButton();

        picker.on('emoji', emoji => {
            document.querySelector('#content').value += emoji;
        });

        button.addEventListener('click', () => {
            picker.pickerVisible ? picker.hidePicker() : picker.showPicker(button);
        });

        $("#but_upload").click(function(){

        var fd = new FormData();
        var files = $('#file')[0].files[0];
        fd.append('file',files);

        $.ajax({
            url: 'upload.php',
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            success: function(response){
                if(response != 0){
                    $("#img").attr("src",response); 
                    $(".preview img").show(); // Display image element
                    send_img(response);
                }else{
                    alert('file not uploaded');
                }
            },
        });
        });

        $("#img_upload").click(function(){

        var fd = new FormData();
        var files = $('#img_file')[0].files[0];
        fd.append('file',files);

        $.ajax({
            url: 'user.php',
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            success: function(response){
                if(response != 0){
                    checkUser();
                    $("#u_img").attr("src",response); 
                    $(".preview img").show(); // Display image element
                    setImage(response);
                }else{
                    alert('file not uploaded');
                }
            },
        });
        });

        function checkUser() {

                if(!isLogin()) {
                    $("#usr_name").html("Not signed in.");
                    $("#usr_status").html("Create an account now!");
                    $("#logout_btn").css('display', 'none');
                    $("#signup_btn").css('display', '');
                    $("#login_btn").css('display', '');
                } else {
                    $("#usr_name").html(localStorage["user"]);
                    $("#usr_status").text(getStatus())
                    $("#logout_btn").css('display', '')
                    $("#login_btn").css('display', 'none')
                    $("#signup_btn").css('display', 'none')
                    $("#usr_img").attr('src', getImage())
                    initData();
                }
        }

        checkUser();
        ParseServer();

        function initData() {
            $.ajax({
                    url: `api/users/${localStorage["user"]}.json`, dataType: 'json', success: function (data, status) {                        
                        localStorage["img"] = data["config"]["img"];
                        localStorage["status"] = data["status"]["current"];
                    }
            });
        }

        function resetImg() {
            $.post("api/avatar.php", { 'user': localStorage["user"], 'img': 'https://dolor.ml/noxy.jpg' });
        }

        function setImage(img) {
            $.post("api/avatar.php", { 'user': localStorage["user"], 'img': img });
            localStorage.clear("img");
            initData();
        }

        function getImage() {
            return localStorage["img"];
        }
        
        function setStatus(str) {
            $.post("api/status.php", { 'user': localStorage["user"], 'status': str });
            $("#usr_status").text(str)
        }

        function resetStatus() {
            $.post("api/status.php", { 'user': localStorage["user"], 'status': 'Hello' });
            $("#usr_status").text("Hello")
        }

        function getStatus() {
            return localStorage["status"];
        }

        function login (user, pass) {
            var log = false;
                $.ajax({
                    url: 'api/logins.json', dataType: 'json', ifModified: true, timeout: 2000, success: function (data, status) {
                        if(!data)
                            return;
                        
                        var error = "";

                        for (let i = 0; i < data.length; i++) {

                            if(data[i][user] != undefined)
                            {
                                $.post("api/login.php", { 'password': pass, 'hashed': data[i][user] }, function (data, status) {
                                    console.log(data);
                                    if(data.includes("True")){
                                        error = "";
                                        log = true;
                                        localStorage["user"] = user;
                                        UIkit.notification("<span uk-icon='icon: check'></span> Login was successful!");
                                        location.reload();

                                    } else {
                                        error = "Failed to login."
                                    }
                                });

                            } else {
                                error = "Failed to login.";
                            }

                            
                        }

                    }
            });
        }

        function logout () {
            localStorage.removeItem("user");
            localStorage.removeItem("img");
            localStorage.removeItem("status");
            location.reload();
        }

        function includes(arr, key) {
            for (var i = 0; i < arr.length; i++) {
                if(arr[i][key] != undefined) return true;
            }
            return false;
        }

        function isLogin() {
            if(localStorage["user"] == undefined)
            {
                return false;

            } else {
                return true;
            }
        }

        function clear() {
            $('ul#messages').html(' ');
        }

        function channel_public() {
            var dir = "public/messages.json"
            sessionStorage["channel"] = dir;
            SetServer("public");
            clear();
            $('ul#messages').data('last_message_id', -1);
            poll_for_new_messages();
        }

        function send_img(url) {
            $.post("api.php", { 'json': sessionStorage["channel"], 'name': localStorage["user"], 'msg': `[img]=>${url}`, 'img': getImage() });
        }

        function NewServer(name) {
            var id = ID();
            $.post("server.php", { 'user': localStorage["user"], 'id': id, 'name': name }, function(data, status) {
                if(data == `Server with the name '${name}' already exists.`){
                    UIkit.notification(`<span uk-icon='icon: warning'></span> ${data}`);
                    return;
                } else {
                UIkit.notification(`<span uk-icon='icon: check'></span> ${data}`);           
                $.post("add_server.php", { 'user': localStorage["user"], 'id': id }, function(data, status) {
                    if(data == "You already have joined the server."){
                        UIkit.notification(`<span uk-icon='icon: warning'></span> ${data}`);
                        return;
                    } else {
                    UIkit.notification(`<span uk-icon='icon: check'></span> ${data}`);
                    }
                });
                }
            });

            setTimeout(function(){location.reload()}, 3000);

        }

        function JoinServer(id) {
            $.post("add_server.php", { 'user': localStorage["user"], 'id': id }, function(data, status) {
                if(data == 'You already have joined the server.'){
                    UIkit.notification(`<span uk-icon='icon: warning'></span> ${data}`);
                    return;
                } else if(data == 'Failed to find server with ID.') {
                    UIkit.notification(`<span uk-icon='icon: warning'></span> ${data}`);
                    return;
                } else {
                    UIkit.notification(`<span uk-icon='icon: check'></span> ${data}`); 
                }
            });

            setTimeout(function(){location.reload()}, 3000);
        }

        function GetServerName(id) {
            return $.ajax({
                type: 'POST',
                url: "get_server.php",
                data: {"id": id},
                dataType: "text",
                async:false
            }).responseText;
        }

        function SetServer(id) {
            if(id != "public"){
            var location = `servers/${GetServerName(id)}-${id}.json`
            sessionStorage["channel"] = location;
            $("#name_srv").html("");
            $("#name_srv").text(GetServerName(id));
            $("#des_srv").html("");
            $("#des_srv").text("The server ID is ''" + id + "''. Tell your friends to use this ID to join your server.");
            $("#del_srv").attr("onclick", `DeleteServer('${id}')`)
            clear();
            $('ul#messages').data('last_message_id', -1);
            poll_for_new_messages();
            } else {
                $("#name_srv").text("public");
                $("#des_srv").html("");
                $("#des_srv").text("Chat all you want here.");
                $("#del_srv").attr("onclick", `UIkit.notification("<span uk-icon='icon: warning'></span> You can't delete the public server...")`);
            }
        }

        function DeleteServer(id) {
            $.post("api/delete_server.php", { 'user': localStorage["user"], 'id': id }, function(data, status) {
                location.reload();
            });
        }

        function ParseServer() {
            if(isLogin()) {
                var div = document.getElementById("usr_servers");
                div.innerHTML = "";
                $.ajax({
                    url: `api/users/${localStorage["user"]}.json`, dataType: 'json', success: function (data, status) {  
                        var arr = data["server"]["servers"].split(">")
                        for (let i = 0; i < arr.length-1; i++) {
                            if(arr[i + 1] != "") {
                                let index = i + 1;
                                let id = arr[index];

                                var button = document.createElement("span");
                                var name = GetServerName(id);

                                button.innerHTML = `<button class="uk-button uk-button-secondary" onclick="SetServer('${id}')"><span uk-icon="icon: home" class="uk-icon"></span> ${name}</button>`;
                                div.appendChild(button);
                            }
                        }
                    }
                });
            }
        }

        function ID() {
            return Math.random().toString(36).substr(2, 9);
        }

        function signup (user, pass) {

            $.ajax({
                    url: 'api/logins.json', dataType: 'json', success: function (data, status) {                        
                        if(!includes(data, user)) {
                            $.post("api/signup.php", { 'user': user, 'pass': pass }, function (data, status) {
                                    console.log(data); 
                                    checkUser();
                                });
                        }
                    }
            });
        }

            $("#form_theme").submit(function(e) {
                e.preventDefault();
            });

            style("body { background-image: url('"+ localStorage["back"] + "') }");

            function style(str) {
                var node = document.createElement('style');
                node.innerHTML = str;
                document.body.appendChild(node);
            }

            function resetTheme() {
            localStorage["back"] = null;
            window.location = window.location;
            }

            function setTheme() {
            var e = document.getElementById("theme");
            var e2 = document.getElementById('back').value;
            localStorage["back"] = e2;
            window.location = window.location;
            }
        

    </script>

</body>

</html>
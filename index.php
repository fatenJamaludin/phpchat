<?php
error_reporting(-1);
ini_set('display_errors', 'On');
?>

<?php
require_once __DIR__ . '/demo.php';
$demo = new Demo();
$admin_id = $demo->getDemoUser();
?>

<html>
    <head>
        <title>Google Cloud Messaging 3.0 | AndroidHive</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://fonts.googleapis.com/css?family=Raleway:400,800,100' rel='stylesheet' type='text/css'>
        <link href='style.css' rel='stylesheet' type='text/css'>
        <link href='http://api.androidhive.info/gcm/styles/default.css' rel='stylesheet' type='text/css'>
        <script type="text/javascript" src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
        <script type="text/javascript" src="http://api.androidhive.info/gcm/highlight.pack.js"></script>
        <script type="text/javascript">
            var user_id = '<?= $admin_id ?>';
            $(document).ready(function () {

                hljs.initHighlightingOnLoad();

                getChatroomMessages($('#topics li:first').attr('id'));


                $('ul#topics li').on('click', function () {
                    $('ul#topics li').removeClass('selected');
                    $(this).addClass('selected');
                    getChatroomMessages($(this).prop('id'))
                });

                function getChatroomMessages(id) {
                    $.getJSON("v1/chat_rooms/" + id, function (data) {
                        var li = '';
                        $.each(data.messages, function (i, message) {
                            li += '<li class="others"><label class="name">' + message.user.username + '</label><div class="message">' + message.message + '</div><div class="clear"></div></li>';
                        });
                        $('ul#topic_messages').html(li);
                        if (data.messages.length > 0) {
                            scrollToBottom('msg_container_topic');
                        }
                    }).done(function () {

                    }).fail(function () {
                        alert('Sorry! Unable to fetch topic messages');
                    }).always(function () {

                    });

                    // attaching the chatroom id to send button
                    $('#send_to_topic').attr('chat_room', id);
                }

                $('#send_to_topic').on('click', function () {
                    var msg = $('#send_to_topic_message').val();
                    if (msg.trim().length === 0) {
                        alert('Enter a message');
                        return;
                    }

                    $('#send_to_topic_message').val('');
                    $('#loader_topic').show();

                    $.post("v1/chat_rooms/" + $(this).attr('chat_room') + '/message',
                            {user_id: user_id, message: msg},
                    function (data) {
                        if (data.error === false) {
                            var li = '<li class="self" tabindex="1"><label class="name">' + data.user.name + '</label><div class="message">' + data.message.message + '</div><div class="clear"></div></li>';
                            $('ul#topic_messages').append(li);
                            scrollToBottom('msg_container_topic');
                        } else {
                            alert('Sorry! Unable to send message');
                        }
                    }).done(function () {

                    }).fail(function () {
                        alert('Sorry! Unable to send message');
                    }).always(function () {
                        $('#loader_topic').hide();
                    });
                });

                $('input#send_to_single_user').on('click', function () {
                    var msg = $('#send_to_single').val();
                    var to = $('.select_single').val();
                    if (msg.trim().length === 0) {
                        alert('Enter a message');
                        return;
                    }

                    $('#send_to_single').val('');
                    $('#loader_single').show();

                    $.post("v1/users/" + to + '/message',
                            {user_id: user_id, message: msg},
                    function (data) {
                        if (data.error === false) {
                            $('#loader_single').hide();
                            alert('Push notification sent successfully! You should see a Toast message on device.');
                        } else {
                            alert('Sorry! Unable to post message');
                        }
                    }).done(function () {

                    }).fail(function () {
                        alert('Sorry! Unable to send message');
                    }).always(function () {
                        $('#loader_single').hide();
                    });
                });

                $('input#send_to_multiple_users').on('click', function () {
                    var msg = $('#send_to_multiple').val();
                    var to = $('.select_multiple').val();

                    if (to === null) {
                        alert("Please select the users!");
                        return;
                    }

                    if (msg.trim().length === 0) {
                        alert('Enter a message');
                        return;
                    }

                    $('#send_to_multiple').val('');
                    $('#loader_multiple').show();

                    var selMulti = $.map($(".select_multiple option:selected"), function (el, i) {
                        return $(el).val();
                    });
                    
                    to = selMulti.join(",");

                    $.post("v1/users/message",
                            {user_id: user_id, to: to, message: msg},
                    function (data) {
                        if (data.error === false) {
                            $('#loader_multiple').hide();
                            alert('Push notification sent successfully to multiple users');
                        } else {
                            alert('Sorry! Unable to send message');
                        }
                    }).done(function () {

                    }).fail(function () {
                        alert('Sorry! Unable to send message');
                    }).always(function () {
                        $('#loader_multiple').hide();
                    });
                });

                function scrollToBottom(cls) {
                    $('.' + cls).scrollTop($('.' + cls + ' ul li').last().position().top + $('.' + cls + ' ul li').last().height());
                }
            });
        </script>
    </head>
    <body>
        <div class="header">
            <label class="logo">ANDROIDHIVE</label>
            <h2>Google Cloud Messaging</h2>
            <h2 class="small">Sending push notifications using Android, PHP & MySQL</h2>
        </div>

        <div class="topics">
            <h2 class="heading">Sending message to a `topic`</h2>
            <pre>
                <code class="php">
require_once __DIR__ . '/libs/gcm/gcm.php';
require_once __DIR__ . '/libs/gcm/push.php';

$gcm = new GCM();
$push = new Push();

// chat room id - which is topic id
$chat_room_id = 4;

// get the user using userid
$user = $db->getUser($user_id);

$data = array();
$data['user'] = $user;
$data['message'] = $response['message'];

$push->setTitle("GCM 3.0 Demo");
$push->setIsBackground(FALSE);
$push->setData($data);

// sending push message to a topic
$gcm->sendToTopic('topic_' . $chat_room_id, $push->getPush());
                </code>
            </pre>
            <div class="box">
                <div class="usr_container">
                    <ul id="topics">
                        <?php
                        $chatrooms = $demo->getAllChatRooms();
                        foreach ($chatrooms as $key => $chatroom) {
                            $cls = $key == 0 ? 'selected' : '';
                            ?>
                            <li id="<?= $chatroom['chat_room_id'] ?>" class="<?= $cls ?>">
                                <label><?= $chatroom['name'] ?></label>
                                <span>topic_<?= $chatroom['chat_room_id'] ?></span>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="msg_container msg_container_topic">
                    <ul id="topic_messages"></ul>
                </div>
                <div class="send_container">
                    <textarea placeholder="Type a message here" id="send_to_topic_message"></textarea>
                    <input id="send_to_topic" type="button" value="Send to Topic"/>
                    <img src="loader.gif" id="loader_topic" class="loader"/>
                </div>
                <div class="clear"></div>
            </div>
            <h2 class="heading">Sending message to `Single User`</h2>
            <pre>
                <code class="php">
require_once __DIR__ . '/libs/gcm/gcm.php';
require_once __DIR__ . '/libs/gcm/push.php';

$gcm = new GCM();
$push = new Push();

$user = $db->getUser($to_user_id);

$data = array();
$data['user'] = $user;
$data['message'] = $response['message'];

$push->setTitle("GCM 3.0 Demo");
$push->setIsBackground(FALSE);
$push->setData($data);

// sending push message to single user
$gcm->send($user['gcm_registration_id'], $push->getPush());
                </code>
            </pre>
            <div class="container">
                <select class="select_single">
                    <?php
                    $users = $demo->getAllUsers();
                    foreach ($users as $key => $user) {
                        ?>
                        <option value="<?= $user['user_id'] ?>"><?= $user['name'] ?> (<?= $user['email'] ?>)</option>
                        <?php
                    }
                    ?>
                </select><br/>
                <textarea id="send_to_single" class="textarea_msg" placeholder="Type a message"></textarea><br/>
                <input id="send_to_single_user" type="button" value="Send to single user" class="btn_send"/>
                <img src="loader.gif" id="loader_single" class="loader"/>
            </div>
            <h2 class="heading">Sending message to `Multiple Users`</h2>
            <pre>
                <code class="php">
require_once __DIR__ . '/libs/gcm/gcm.php';
require_once __DIR__ . '/libs/gcm/push.php';

$user = $db->getUser($user_id);
$users = $db->getUsers($to_user_ids);
    
$registration_ids = array();
    
// preparing gcm registration ids array
foreach ($users as $u){
    array_push($registration_ids, $u['gcm_registration_id']);
}

// insert messages in db
// send push to multiple users
$gcm = new GCM();
$push = new Push();

$data = array();
$data['user'] = $user;
$data['message'] = $message;

$push->setTitle("GCM 3.0 Demo");
$push->setIsBackground(FALSE);
$push->setData($data);

// sending push message to multiple users
$gcm->sendMultiple($registration_ids, $push->getPush());
</code>
            </pre>
            <div class="container">
                <select multiple class="select_multiple">
                    <?php
                    foreach ($users as $key => $user) {
                        ?>
                        <option value="<?= $user['user_id'] ?>"><?= $user['name'] ?> (<?= $user['email'] ?>)</option>
                        <?php
                    }
                    ?>
                </select>
                <br/>
                <textarea id="send_to_multiple" class="textarea_msg" placeholder="Type a message"></textarea><br/>
                <input id="send_to_multiple_users" type="button" value="Send to multiple users" class="btn_send"/>
                <img src="loader.gif" id="loader_multiple" class="loader"/>
            </div>
        </div>
        <br/><br/>
        <br/><br/>
        <br/><br/>
    </body>
</html>

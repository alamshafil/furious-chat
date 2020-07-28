function sendMessage(m)
{
  $.post("https://dolor.ml/furiouschat/api.php", { 'json': sessionStorage["channel"], 'name': "Senko Poggers", 'msg': m });
}

function BotParse(message, user)
{
    var args = message.split(" ");

    switch (args[0]) {
        case "sp!poggers":
            sendMessage("[youtube]=>https://www.youtube.com/watch?v=Daz-SRrHdzk");
            break;
        case "sp!help":
            sendMessage("sp!help - shows this message");
            sendMessage("sp!poggers - sends senko poggers");
            break;

        default:
            break;
    }

}
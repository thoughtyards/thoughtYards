var http_request;
var input_cmd;
var focus_id = "<?= (!isset($_SESSION['shcmd']['user']) && isset($_SESSION['shcmd']['login'])) ? "passw" : "input" ?>";

function httpRequest() {

    http_req = false;

    if (window.XMLHttpRequest) { // Mozilla

        http_req = new XMLHttpRequest();

        if (http_req.overrideMimeType)
            http_req.overrideMimeType('text/plain');

    } else if (window.ActiveXObject) { // IE

        try {
            http_req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                http_req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
        }
    }

    return http_req;
}

function update_page() {
    if (http_request.readyState == 4) {
        if (http_request.status == 200) {

            ret = http_request.responseText.split("\r");

            out = ret[1]; // command output
            prm = ret[0]; // prompt

            history_sel = document.getElementById('history_select');

            if (input_cmd &&
                (out.substr(1, <?= strlen($pr_login) ?>) != "<?= $pr_login ?>") &&
                (out.substr(1, <?= strlen($pr_pass) ?>) != "<?= $pr_pass ?>")
            ) {
                exists = false;
                for (i = 1; i < history_sel.length - 1; i++)
                    if (history_sel.options[i].value == input_cmd) {
                        exists = true;
                        break;
                    }

                if (!exists) {
                    hist_count = history_sel.length;
                    last_value = history_sel.options[hist_count - 1].value;
                    last_text = history_sel.options[hist_count - 1].text;
                    history_sel.length++;
                    history_sel.options[hist_count].value = last_value;
                    history_sel.options[hist_count].text = last_text;
                    history_sel.options[hist_count - 1].value = input_cmd;
                    history_sel.options[hist_count - 1].text =
                      (input_cmd.length > <?= $history_chars ?>)
                        ? input_cmd.substr(0, <?= ($history_chars - 3) ?>) + "..."
                        : input_cmd;
                }
            }

            first_word = input_cmd;
            if (first_word.indexOf(" ") > -1)
                first_word = first_word.substr(0, first_word.indexOf(" "));

            if ((first_word == "clear") || (first_word == "exit"))
                document.getElementById('output').value = "";
            else
                document.getElementById('output').value += out;

            document.getElementById('prompt').innerHTML = (first_word == "exit")
              ? "<?= $pr_login ?>" : prm;

            if (prm == "<?= $pr_pass ?>") {
                document.getElementById('div_pass').style.visibility = "visible";
                document.getElementById('input').value = "";
                focus_id = "passw";
            } else {
                document.getElementById('div_pass').style.visibility = "hidden";
                document.getElementById('passw').value = "";
                focus_id = "input";
            }

            if (first_word == "exit") {
                last_option = history_sel.options[history_sel.length - 1];
                history_sel.length = 2;
                history_sel.options[1] = last_option;
                history_sel.selectedIndex = 0;
            }

            document.getElementById('history_cell').style.visibility =
              ((prm == "<?= $pr_login ?>") || (prm == "<?= $pr_pass ?>") || (first_word == "exit"))
                ? "hidden" : "visible";
        }

        focus_id = focus_id ? focus_id : "input";

        document.getElementById('input').value = "";
        document.getElementById('ajax_loading').style.visibility = "hidden";
        document.getElementById(focus_id).focus();
        document.getElementById('output').scrollTop = document.getElementById('output').scrollHeight;
    }
}

function ajax_action(url, func) {
    http_request = httpRequest();

    if (!http_request) {
        alert('Giving up :( Cannot create an XMLHTTP instance');
        return false;
    }

    document.getElementById('ajax_loading').style.visibility = "visible";
    http_request.onreadystatechange = func;

    http_request.open("GET", url, true);
    http_request.setRequestHeader("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
    http_request.setRequestHeader("Cache-Control", "post-check=0, pre-check=0");
    http_request.setRequestHeader("Pragma", "no-cache");
    http_request.send(null);
}

function input_focus() {
    document.getElementById('div_pass').style.visibility = (focus_id == "passw")
      ? "visible" : "hidden";
    document.getElementById(focus_id).focus();
}

function selection_to_clipboard() { // Auto copy selected text. IE only!
    if (window.clipboardData && document.selection)
        window.clipboardData.setData("Text", document.selection.createRange().text);
}

function execute_cmd(cmd_pass, cmd) {
    cmd = cmd_pass ? cmd_pass : cmd;
    cmd = cmd.replace(/\s+/g, " ").replace(/^\s+/g, "").replace(/\s+$/g, "");
    input_cmd = cmd;
    document.getElementById('output').focus();
    ajax_action("<?= $self ?>?cmd=" + escape(cmd), update_page);
    return false;
}

function get_from_history(history_sel) {
    option = history_sel.options[history_sel.selectedIndex];
    if (option.value) {

        // " " option value indicates that "clear history" selected
        if (option.value == " ") {
            last_option = history_sel.options[history_sel.length - 1];
            history_sel.length = 2;
            history_sel.options[1] = last_option;
            history_sel.selectedIndex = 0;
        } else {
            history_sel.selectedIndex = 0;
            document.getElementById('input').value = option.value;
            document.getElementById('input').focus();
        }
    }
}

if (window.clipboardData) // Right click pastes the clipboard. IE only
    document.oncontextmenu = new Function("document.getElementById('input').value = window.clipboardData.getData('Text'); input_focus(); return false");

<?php
$table = isset($_GET['table']) ? $_GET['table'] : "sample";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ACE Autocompletion demo</title>
  <style type="text/css" media="screen">
    body {
        overflow: hidden;
    }
    
    #editor { 
        margin: 0;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
    }
  </style>
</head>
<body>
<div></div>
<pre id="editor"></pre>

<!-- load ace -->
<script src="./src-noconflict/ace.js"></script>
<!-- load ace language tools -->
<script src="./src-noconflict/ext-language_tools.js"></script>
<script>
    // trigger extension
    ace.require("ace/ext/language_tools");
    var editor = ace.edit("editor");
    editor.session.setMode("ace/mode/sql");
    editor.setTheme("ace/theme/tomorrow");
    // enable autocompletion and snippets
    editor.setOptions({
        enableBasicAutocompletion: true,
        enableSnippets: true,
        enableLiveAutocompletion: true
    });
</script>

<script>
	if (typeof ace == "undefined" && typeof require == "undefined") {
    document.body.innerHTML = "<p style='padding: 20px 50px;'>couldn't find ace.js file, <br>"
        + "to build it run <code>node Makefile.dryice.js full<code>"
} else if (typeof ace == "undefined" && typeof require != "undefined") {
    require(["ace/ace"], function() {
        setValue();
    });
    require(["ace/config"], function() {
        var config = require("ace/config");
        config.setLoader(function(moduleName, cb) {
            require([moduleName], function(module) {
                cb(null, module);
            });
        });
    })
} else {
    require = ace.require;
    setValue()
}

function setValue() {
    require("ace/lib/net").get(document.baseURI, function(text) {
        var el = document.getElementById("editor");
        el.env.editor.session.setValue("SELECT * FROM <?=$table;?>");
    });
    loadAceLinters();
}

function loadAceLinters() {
    if (typeof  define == "function" && define.amd) {
         require([
            "https://mkslanc.github.io/ace-linters/build/ace-linters.js"
        ], function(m) {
            addLinters(m.LanguageProvider);
        });
    } else {
        require("ace/lib/net").loadScript(
            "https://mkslanc.github.io/ace-linters/build/ace-linters.js", 
            function() {
                addLinters(window.LanguageProvider);
            }
        ) 
    }
    function addLinters(LanguageProvider) {
        var languageProvider = LanguageProvider.fromCdn("https://mkslanc.github.io/ace-linters/build", {
            functionality: {
                hover: true,
                completion: {
                    overwriteCompleters: false
                },
                completionResolve: true,
                format: true,
                documentHighlights: true,
                signatureHelp: false
            }
        });
        window.languageProvider = languageProvider;
        document.querySelectorAll(".ace_editor").forEach(function(el) {
            var editor = el.env && el.env.editor;
            if (editor) {
                editor.setOption("enableBasicAutocompletion", true)
                languageProvider.registerEditor(editor);
            }
        });
    }
}

window.addEventListener('message', function(event) {
	// Check the origin of the sender (for security)
	/*if (event.origin !== 'http://loc') {
		return; // Ignore messages from unexpected origins
	}*/

	// Do something with the received value
	var receivedValue = event.data;
	if (typeof receivedValue == "string") {
		try{
			let json = JSON.parse(receivedValue);
			if (json.type == "query") {
				editor.setValue(json.query);
			}
			else{
				//var el = document.getElementById("editor");
		        //el.env.editor.session.setValue("SELECT * FROM visitors");
				// Value you want to send
				var valueToSend = editor.getValue();

				// Send the value to the parent window
				parent.postMessage(JSON.stringify({type:"query", query:valueToSend}), '*');
			}
		}
		catch(E){
			//do nothing
		}
	}
	else{
		console.log(receivedValue);
	}
});
</script>
</body>
</html>

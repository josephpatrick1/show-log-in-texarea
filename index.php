<?php

mb_internal_encoding("UTF-8");

if (isset($_POST['p']) && $_POST['p'] == "updatelog") {

    $text = file_get_contents("log.txt");
    $hash = md5($text);
    $length = mb_strlen($text);

    $client_hash = $_POST['hash'];
    $client_length = $_POST['length'];

    // Verifica se houve alguma modificação na parte mostrada do log
    if (md5(mb_substr($text, 0, $client_length)) == $client_hash) {

        // Verificar se foi adicionado alguma coisa após a parte mostrada do log
        if ($length != $client_length) {
            $result = array(
                'type' => 'append',
                'text' => mb_substr($text, $client_length, $length - $client_length),
                'hash' => $hash,
                'length' => $length
            );
        }
        // Log continua exatamente igual
        else {
            $result = array(
                'type' => 'ok'
            );
        }
    }
    // Mudança de log, recarrega todo o texto
    else {
        $result = array(
            'type' => 'change',
            'text' => $text,
            'hash' => $hash,
            'length' => $length
        );
    }

    echo json_encode($result);
} else {
?>

    <!DOCTYPE html>
    <html lang="pt">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Teste Console</title>
        <style>
            #logtext {
                margin: 0px;
                width: 100%;
                height: 400px;
                color: #eeeeee;
                background: #111111;
            }
        </style>
    </head>

    <body>
        <textarea id="logtext"> Log aqui </textarea>
    </body>

    </html>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <script>
        let text = "";
        let hash = "";
        let length = text.length;

        function atualizarLog() {

            $.ajax({
                type: "POST",
                data: {
                    "p": "updatelog",
                    "hash": hash,
                    "length": length
                },
                success: function(result) {
                    if (result.type == "append") {

                        console.log("Adicionar:\n", result.text);

                        text = text + result.text;
                        hash = result.hash;
                        length = result.length;

                        $("#logtext").val(text);

                        ScrollDownTextArea($("#logtext"));
                    }
                    if (result.type == "change") {

                        console.log("Alterar para:\n", result.text);

                        text = result.text;
                        hash = result.hash;
                        length = result.length;

                        $("#logtext").val(text);

                        ScrollDownTextArea($("#logtext"));
                    }
                    setTimeout(function() {
                        atualizarLog();
                    }, 2000);
                },
                error: function(error) {
                    console.log("Erro na leitura do Log");
                    setTimeout(function() {
                        atualizarLog();
                    }, 2000);
                },
                dataType: "JSON"
            });
        }

        function ScrollDownTextArea(textarea) {
            textarea = $(textarea);
            if (textarea.length)
                textarea.scrollTop(textarea[0].scrollHeight - textarea.height());
        }

        atualizarLog();
    </script>
<?php
}
?>
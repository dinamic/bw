<?php
session_start();
session_destroy();
session_start();
?>
<html>
    <head>
        <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.flot.js"></script>
        
        <script id="source" language="javascript" type="text/javascript">
        $(document).ready(function() {
            var options = {
                lines: { show: true },
                points: { show: true },
                xaxis: { mode: "time" }
            };
            var data = [];
            var placeholder = $("#placeholder");

            $.plot(placeholder, data, options);

            var iteration = 0;

            function fetchData() {
                ++iteration;

                function onDataReceived(series) {
                    // we get all the data in one go, if we only got partial
                    // data, we could merge it with what we already got
                    data = [ series ];
                    
                    $.plot($("#placeholder"), data, options);
                }

                $.ajax({
                    url: "data.php",
                    method: 'GET',
                    dataType: 'json',
                    success: onDataReceived
                });
                
                setTimeout(fetchData, 1060);
            }

            setTimeout(fetchData, 1000);
        });

    </script>
    </head>
    <body>
    <div id="placeholder" style="width:600px;height:300px;"></div>

    </body>
</html>

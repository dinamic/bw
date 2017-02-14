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
            var renderInterfaceGraph = function (interfaceName) {
                var options = {
                    lines: { show: true },
                    points: { show: true },
                    xaxis: { mode: "time" }
                };

                var data = [];

                var placeholderName = "placeholder_" + interfaceName;

                var html = "<div id=\"" + placeholderName + "\" style=\"width:600px;height:300px;\"></div>";
                var placeholder = $(html);

                placeholder.appendTo($('body'));

                $.plot(placeholder, data, options);

                var iteration = 0;

                function fetchData() {
                    ++iteration;

                    function onDataReceived(series) {
                        // we get all the data in one go, if we only got partial
                        // data, we could merge it with what we already got
                        data = [ series ];

                        $.plot(placeholder, data, options);
                    }

                    $.ajax({
                        url: "data.php?interface=" + interfaceName,
                        method: 'GET',
                        dataType: 'json',
                        success: onDataReceived
                    });

                    setTimeout(fetchData, 1000);
                }

                fetchData();
            };

            renderInterfaceGraph('eth0');
        });
        </script>
    </head>
    <body>
        <h1>bandwidth monitor written in php and javascript</h1>
    </body>
</html>

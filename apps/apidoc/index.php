<?php
    $json = file_get_contents('endpoints.json');
    $paths = json_decode($json, true);
    $counter = 0;
?>
<!doctype html>
<html lang="en">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Vortech API v1.0 Documentation</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
        integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
</head>
<body>
    <div class="container-fluid">
        <h1>Vortech API documentation</h1>
        <hr />
        <p>All the available endpoints and their methods are listed below. If it does not appear below, it has not
            been implemented. Click on a header to show the details.</p>

        <?php foreach ($paths as $baseRoute) { ?>
        <div class="panel-group" id="accordion">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $counter; ?>">
                            <?php echo $baseRoute['name']; ?>
                        </a>
                    </h4>
                </div>
                <div id="collapse<?php echo $counter; ?>" class="panel-collapse collapse">
                    <div class="panel-body">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>Title</th>
                                    <th>URI</th>
                                    <th>Method</th>
                                    <th>URL params</th>
                                    <th>Request body</th>
                                    <th>Response codes</th>
                                </tr>
                                <?php foreach ($baseRoute['endpoints'] as $endpoint) { ?>
                                <tr>
                                    <td class="col-md-2"><?php echo $endpoint['title']; ?></td>
                                    <td class="col-md-1"><pre><?php echo $endpoint['uri']; ?></pre></td>
                                    <td class="col-md-1"><?php echo $endpoint['method']; ?></td>
                                    <td class="col-md-1"><?php echo $endpoint['urlParams']; ?></td>
                                    <td class="col-md-5"><code><?php echo $endpoint['body']; ?></code></td>
                                    <td class="col-md-2">
                                    <?php foreach ($endpoint['codes'] as $code) { ?>
                                        <?php echo $code['text']; ?> (<?php echo $code['code']; ?>),
                                    <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php $counter++; ?>
        </div>
        <?php } ?>

    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>

</body>
</html>

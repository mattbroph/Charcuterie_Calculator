<?php
    session_start();
    require_once('pagetitles.php');
    $page_title = CR_HOME_PAGE;
?>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= $page_title ?></title>
        <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css"
          integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS"
          crossorigin="anonymous">
        <link rel="stylesheet"
          href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
          integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf"
          crossorigin="anonymous">
    </head>
    <body>
        <!-- Nav Menu -->
        <?php
        require_once('navmenu.php');
        ?>
        <div class="card">
            <div class="card-body m-3">
                <h1><?= $page_title ?></h1>
                <hr/>
                <?php
                require_once('dbconnection.php');
                require_once('recipeimagefileutil.php');
                require_once('recipefileconstants.php');

                // Query the database for recipe post data and output onto index page                      
                $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
                        or trigger_error('Error connecting to MySQL server for
                        DB_NAME.' , E_USER_ERROR);

                $query = "SELECT `recipe_id`, `title`, `description`, `image_file` FROM recipe ORDER BY recipe_id";

                $result = mysqli_query($dbc, $query)
                        or trigger_error('Error querying database Charcuterie' 
                        , E_USER_ERROR);
                    

                if (mysqli_num_rows($result) > 0)
                {
                    
                ?>
                <div class="row">
                <?php
                    while ($row = mysqli_fetch_assoc($result))
                    {

                        $recipe_image_file = htmlspecialchars($row['image_file']);

                    
                        if (empty($recipe_image_file))
                        {
                            $recipe_image_file_displayed = CR_UPLOAD_PATH . CR_DEFAULT_RECIPE_FILE_NAME;
                        }
                        else
                        {
                            $recipe_image_file_displayed = $recipe_image_file;
                        }
                        echo "<div class='col-md-3 mb-4 col-sm-12'
                                    style='display: flex; justify-content: space-around'>
                                <div class='card' style='width: 18rem;'>
                                <img src='" . $recipe_image_file_displayed . "' class='card-img-top'
                                        alt='recipe Image' style='height: 200px; object-fit: contain;'>
                                    <div class='card-body'>
                                        <h5 class='card-title'>" . htmlspecialchars($row['title']) . "</h5>                                        
                                        </h6><p class='card-text text-truncate' style='max-height: 50px;'>"
                                        . htmlspecialchars($row['description']) . "<br/></p><a href='viewrecipe.php?id="
                                        . htmlspecialchars($row['recipe_id']) 
                                        . "' class='btn' style='background-color: #472eab; color: #ffffff;'>View Recipe</a>
                                    </div>
                                </div>
                            </div>";
                    }
                }
                ?>
       
                <script>
                    // JavaScript for disabling form submissions if there are invalid fields
                    (function() {
                    'use strict';
                    window.addEventListener('load', function() {
                        // Fetch all the forms we want to apply custom Bootstrap validation styles to
                        var forms = document.getElementsByClassName('needs-validation');
                        // Loop over them and prevent submission
                        var validation = Array.prototype.filter.call(forms, function(form) {
                        form.addEventListener('submit', function(event) {
                            if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                        });
                    }, false);
                    })();
                </script>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
            integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
            crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"
            integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut"
            crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"
            integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k"
            crossorigin="anonymous"></script>
    </body>
</html>

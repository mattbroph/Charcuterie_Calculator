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
                <h1><?= $page_title ?> - View Recipe</h1>
                <hr/>
                <?php
                require_once('dbconnection.php');
                require_once('recipeimagefileutil.php');
                require_once('recipefileconstants.php');
                require_once('queryutils.php');

                // Initialize $id variable
                $id = '';

                // Check to see if id is set via GET
                if (isset($_GET['id']))
                {
                    $id = $_GET['id'];
                }

                // Check to see if id is set via POST
                if (isset($_POST['id']))
                {
                    $id = $_POST['id'];
                }
                
                // If Admin is logged in show delete and edit links
                if (isset($_SESSION['user_access_privileges'])
                            && $_SESSION['user_access_privileges'] == 'admin')
                {
                ?>
                    <!-- Edit or Delete Links if admin is logged in-->
                    <a class='nav-link' href='editrecipe.php?id_to_edit=<?= $id ?>'>Edit Recipe</a>
                    <a class='nav-link' href='deleterecipe.php?id_to_delete=<?= $id ?>'>Delete Recipe</a>
                    <hr/>
                    <br/>
                <?php
                }

                // If id was set via post or get, go into code
                if (!empty($id))
                {                   
                    // Connect to the database                     
                    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
                            or trigger_error('Error connecting to MySQL server for
                            DB_NAME.' , E_USER_ERROR);

                    // Query the database for recipe post data
                    $query = "SELECT * FROM recipe WHERE recipe_id = ?";


                    $results = parameterizedQuery($dbc, $query, 'i', $id)
                            or trigger_error(mysqli_error($dbc), E_USER_ERROR);

                    // Query the database for recipe ingredients
                    $query_two = "SELECT * FROM ingredient WHERE recipe_id = ?";

                    $results_two = parameterizedQuery($dbc, $query_two, 'i', $id)
                            or trigger_error(mysqli_error($dbc), E_USER_ERROR);

                        // If a row was found with recipe_id and there are ingredients                 
                        if (mysqli_num_rows($results) == 1 && mysqli_num_rows($results_two) > 0)
                        {
                            $row = mysqli_fetch_assoc($results);

                            $recipe_image_file = $row['image_file'];

                            // Assign default recipe image file is none in the database
                            if (empty($recipe_image_file))
                            {
                                $recipe_image_file = CR_UPLOAD_PATH . CR_DEFAULT_RECIPE_FILE_NAME;
                            }                          
                            
                            // Sanitize data before displaying in html
                            $row['title'] = htmlspecialchars($row['title']);
                            $row['description'] = htmlspecialchars($row['description']);
                            $row['image_file'] = htmlspecialchars($recipe_image_file);


                        ?>
                <div class="row">
                    <div class='col-md-6' style="border-right: 1px solid #ccc;">
                        <div class="row" style="margin-left: 3em; margin-right: 4em;">
                            <div class="col-6">
                                <h2><?= $row['title'] ?></h2>
                                <br/>
                                <h4 style="font-size: 20px;"><?= $row['description'] ?></h4>
                            </div>
                            <div class="col-6 d-flex justify-content-end">
                                <img src="<?= $recipe_image_file ?>" class="img-thumbnail"
                                    style="max-height: 200px;" alt="Recipe post image">
                            </div>
                            <div class="col-6">
                                <h2>Instructions</h2>
                                <p><?= $row['instructions'] ?></p>
                            </div>
                        </div>
                        <br/>
                        <br/>
                    </div>
                    <hr>                        
                    <div class='col-md-6'>
                    <div class="row" style="margin-left: 4em;">
                            <form enctype="multipart/form-data" class="needs-validation" novalidate method="POST"
                                action="<?= $_SERVER['PHP_SELF'] . '?id=' . $id ?>">
                                <!-- Weight for recipe -->
                                <label for="recipe_weight"
                                    class="col-form-label-lg">How many grams of <?= $row['title'] ?> would you like to make?</label>
                                    <input type="number" class="form-control" id="recipe_weight"
                                        name ="recipe_weight" placeholder="Weight in grams"
                                        required>
                                    <div class="invalid-feedback">
                                        Please enter a valid weight in grams.
                                    </div>
                                <br/>
                                <!-- Button -->
                                <button class="btn" style='background-color: #472eab; color: #ffffff;'
                                        type="submit"
                                        name="weight_submission">Calculate Recipe</button>
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <br/>
                                <hr/>
                            </form>
                        </div>
                        <!-- Recipe Table -->
                        <div class="row" style="margin-left: 4em; margin-right: 4em;">
                            <h2><?= $row['title'] ?> Recipe</h2>
                        </div>
                            <?php
                                if (isset($_POST['weight_submission']))
                                {
                                    $recipe_weight = filter_var($_POST['recipe_weight'], FILTER_SANITIZE_NUMBER_INT);
                                ?>
                            <div class="row" style="margin-left: 4em; margin-right: 4em;">
                                </br>
                                </br>
                                <h3>Total Weight: <?= $recipe_weight ?> grams</h3>
                            </div>
                            <?php
                                }
                                else
                                {
                                    $recipe_weight = 1000;
                                ?>
                                <div class="row" style="margin-left: 4em; margin-right: 4em;">
                                    </br>
                                    </br>
                                    <h3>Total Weight: <?= $recipe_weight ?> grams</h3>
                                </div>
                                <?php
                                }
                            ?>
                        
                        <div class="row" style="margin-left: 4em; margin-right: 4em;">
                            <div class="col">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th>Ingredient</th>
                                        <th>Weight</th>
                                    </tr>

                        <?php
                            if (isset($_POST['weight_submission']))
                            {
                                while ($row_two = mysqli_fetch_assoc($results_two))
                                {
                                    $recipe_weight = htmlspecialchars($_POST['recipe_weight']);

                                    $ingredient = htmlspecialchars($row_two['ingredient']);
                                    $percentage = htmlspecialchars($row_two['percentage']);
    
                                    $ingredient_weight = $recipe_weight * ($percentage / 100);
    
                                    echo "<tr><td>" . $ingredient . "</td>";
                                    echo "<td>" . $ingredient_weight . "g</td>";
                                }
                            }
                            else
                            {
                                while ($row_two = mysqli_fetch_assoc($results_two))
                                {
                                    $recipe_weight = 1000;

                                    $ingredient = htmlspecialchars($row_two['ingredient']);
                                    $percentage = htmlspecialchars($row_two['percentage']);

                                    $ingredient_weight = $recipe_weight * ($percentage / 100);

                                    echo "<tr><td>" . $ingredient . "</td>";
                                    echo "<td>" . $ingredient_weight . "g</td>";
                                }
                            }
                        ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                    <?php
                        // End if statement if a row was found with recipe_id
                        }
                        // If ID does not exist
                        else
                        {
                            header("Location: unauthorizedaccess.php");
                            exit;
                        }
                // End if statement if $_GET['id'] was set
                }
                // If id was not set, send them to unauthorized access page
                else
                {
                    header("Location: unauthorizedaccess.php");
                    exit;
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
<?php
    require_once('authorizeaccess.php');
    require_once('pagetitles.php');
    $page_title = CR_DELETE_RECIPE_PAGE;
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
    </head>
    <body>
        <!-- Nav Menu -->
        <?php
            require_once('navmenu.php');
        ?>
        <div class="card">
            <div class="card-body m-3">
                <h1>Delete A Recipe</h1>
                <?php
                require_once('dbconnection.php');
                require_once('queryutils.php');

                $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
                        or trigger_error(
                        'Error connecting to MySQL server for' . DB_NAME,
                        E-USER_ERROR
                        );

                    // If form button was hit to delete entry
                    if (isset($_POST['delete_recipe_submission']) && isset($_POST['id']))
                    {

                        $id = $_POST['id'];

                        $query = "DELETE FROM recipe WHERE recipe_id = ?";

                        $results = parameterizedQuery($dbc, $query, 'i', $id)
                        or trigger_error(mysqli_error($dbc), E_USER_ERROR);

                            echo "<h2>Recipe was deleted</h2>";

                            // header("Location: viewprofile.php?id=" . $_SESSION['user_id']);
                            exit;
                    }
                    

                    // If do not delete button was hit
                    elseif (isset($_POST['do_not_delete_recipe_submission']))
                    {

                        header("Location: index.php");
                        exit;
                    }

                    /* If first time landing on the page, validate that the user
                     * is an admin. Display the entry they chose to delete
                     * and give them the option to delete or not.                    
                    */
                    elseif (isset($_GET['id_to_delete']))
                    {
                        ?>
                        <h3 class="text-danger">Confirm Deletion of the Following
                            Recipe:</h3><br/>
                        <?php

                            // Assign id to delete to local variable                        
                            $id_to_delete = $_GET['id_to_delete'];

                            // Query the DB for the recipe
                            $query = "SELECT * FROM recipe WHERE recipe_id = ?";

                            $results = parameterizedQuery($dbc, $query, 'i', $id_to_delete)
                                    or trigger_error(mysqli_error($dbc), E_USER_ERROR);

                            $query_two = "SELECT * FROM ingredient WHERE recipe_id = ?";

                            $results_two = parameterizedQuery($dbc, $query_two, 'i', $id_to_delete)
                            or trigger_error(mysqli_error($dbc), E_USER_ERROR);

                                // If recipe ID was found and ingredients exist
                                if (mysqli_num_rows($results) == 1
                                        && mysqli_num_rows($results_two) > 0)
                                {
                                    $row = mysqli_fetch_assoc($results);

                                    // Sanitize recipe data before displaying in html
                                    $row['title'] = htmlspecialchars($row['title']);
                                    $row['description'] = htmlspecialchars($row['description']);
                                    $recipe_image_file = htmlspecialchars($row['image_file']);

                                    // Display HTML of recipe data here
                                ?>
                                <div class="row">
                                    <div class='col-md-6' style="border-right: 1px solid #ccc;">
                                        <div class="row" style="margin-left: 3em; margin-right: 4em;">
                                            <div class="col-6">
                                                <h2><?= $row['title'] ?></h2>
                                                <h4 style="font-size: 20px;"><?= $row['description'] ?></h4>
                                            </div>
                                            <div class="col-6 d-flex justify-content-end">
                                                <img src="<?= $recipe_image_file ?>" class="img-thumbnail"
                                                    style="max-height: 200px;" alt="Recipe post image">
                                            </div>
                                            <div class="col-6">
                                                <h4 style="font-size: 20px;">Instructions</h4>
                                                <p> <?= $row['instructions'] ?> </p>                
                                            </div>
                                        </div>                            
                                        <br/>
                                        <br/>
                                        <!-- Display form to delete or not delete the entry -->
                                        <form method = "POST"
                                                action="<?= $_SERVER['PHP_SELF'] ?>">
                                            <div class="form-group row">
                                                <div class="col-sm-2">
                                                    <button class="btn btn-danger" type="submit"
                                                            name="delete_recipe_submission">
                                                        Delete Recipe
                                                    </button>
                                                </div>
                                                <div class="col-sm-2">
                                                    <button class="btn" type="submit"
                                                            name="do_not_delete_recipe_submission"
                                                            style='background-color: #472eab; color: #ffffff;'>
                                                        Don't Delete
                                                    </button>
                                                </div>
                                                <input type="hidden" name="id" value="<?= $_GET['id_to_delete'] ?>">
                                            </div>
                                        </form>
                                    </div>
                                    <!--  Display html of recipe ingredients table here -->
                                    <div class='col-md-6'>
                                        <div class="row" style="margin-left: 4em; margin-right: 4em;">
                                            <div class="col">
                                                <h2><?= $row['title'] ?> Recipe:</h2>
                                                <table class="table table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <th>Ingredient</th>
                                                            <th>Percentage</th>
                                                        </tr>
                                                <?php

                                                while ($row_two = mysqli_fetch_assoc($results_two))
                                                {
                                                    // Sanitize data from the database
                                                    $ingredient = htmlspecialchars($row_two['ingredient']);
                                                    $percentage = htmlspecialchars($row_two['percentage']);
                
                                                    echo "<tr><td>" . $ingredient . "</td>";
                                                    echo "<td>" . $percentage . "%</td>";
                                                }                                    
                                                ?>
                                                    </tbody>
                                                </table>
                                                </div>
                                            </div>
                                            </div>
                                <!-- </div> -->

                                            <?php
                                        

                            }                                
                            else
                            {
                            ?>
                                <h3>No Recipe Was Found</h3>
                            <?php
                            }
                    }                    
                    ?>
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
<?php
    require_once('authorizeaccess.php');
    require_once('pagetitles.php');
    $page_title = CR_EDIT_RECIPE_PAGE;
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= $page_title ?></title>
        <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css"
          integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS"
          crossorigin="anonymous">
        <!-- TinyMCE -->
        <script src="https://cdn.tiny.cloud/1/hn7e6euxbc7ezcdevhneq9ca9me4kj5p6pehnb8m7920f3py/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            tinymce.init({
            selector: '#mytextarea'
            });
        </script>
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
                require_once('queryutils.php');

                $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
                        or trigger_error(
                        'Error connecting to MySQL server for' . DB_NAME, E-USER_ERROR);

                // If id to edit was set via GET
                // This is when the admin first lands on the page
                if (isset($_GET['id_to_edit']))
                {
                    $id_to_edit = $_GET['id_to_edit'];                

                    $query = "SELECT * FROM recipe WHERE recipe_id = ?";

                    $results = parameterizedQuery($dbc, $query, 'i', $id_to_edit)
                            or trigger_error(mysqli_error($dbc), E_USER_ERROR);
                    
                    $query_two = "SELECT * FROM ingredient WHERE recipe_id = ?";

                    $results_two = parameterizedQuery($dbc, $query_two, 'i', $id_to_edit)
                            or trigger_error(mysqli_error($dbc), E_USER_ERROR);                    
                    
                    // If the recipe is found
                    if (mysqli_num_rows($results) == 1 && mysqli_num_rows($results_two) > 0)
                    {
                        $row = mysqli_fetch_assoc($results);

                        $title = htmlspecialchars($row['title']);
                        $description = htmlspecialchars($row['description']);
                        $recipe_image_file = htmlspecialchars($row['image_file']);
                        $instructions = $row['instructions'];           

                        // Assign default recipe image file is none in the database
                        if (empty($recipe_image_file))
                        {
                            $recipe_image_file = CR_UPLOAD_PATH . CR_DEFAULT_RECIPE_FILE_NAME;
                        }
                    }
                }
                // If the edit form was submitted
                elseif (isset($_POST['edit_recipe_submission']))
                {

                    // Set local variables to form fields submitted
                    $title = isset($_POST['recipe_title']) ? $_POST['recipe_title'] : '';
                    $description = isset($_POST['recipe_description']) ? $_POST['recipe_description'] : '';
                    $recipe_image_file = isset($_POST['recipe_image_file']) ? $_POST['recipe_image_file'] : '';
                    $id_to_edit = $_POST['id_to_edit'];
                    $ingredient_names = $_POST['ingredient_name'];
                    $percentages = $_POST['percentage'];
                    $recipe_instructions = $_POST['recipe_instructions'];

                    // If user recipe image file is empty, set it to the default image file
                    // Otherwise, assign it to the local variable
                    if (empty($recipe_image_file))
                    {
                        $recipe_image_file_displayed = CR_UPLOAD_PATH . CR_DEFAULT_RECIPE_FILE_NAME;
                    }
                    else
                    {
                        $recipe_image_file_displayed = $recipe_image_file;
                    }

                    // Validate the recipe file image
                    $file_error_message = validateRecipeImageFile();

                    if (empty($file_error_message))
                    {
                        $recipe_image_file_path = addRecipeImageFileReturnPathLocation();

                        // If new image selected, set it to be updated in the database.
                        if (!empty($recipe_image_file_path))
                        {
                            // If replacing an image (other than the default), remove it
                            if (!empty($recipe_image_file))
                            {
                                removeRecipeImageFile($recipe_image_file);
                            }
                            $recipe_image_file = $recipe_image_file_path;
                        
                        }

                    // Once the image file is validated, we can continue into our
                    // query to edit the database
                    $query = "UPDATE recipe SET `title` = ?, `description` = ?, "
                            . "`image_file` = ?, `instructions` = ? " . "WHERE recipe_id = ?";
                    
                    $results = parameterizedQuery($dbc, $query, 'ssssi', $title,
                            $description, $recipe_image_file, $recipe_instructions, $id_to_edit)
                            or trigger_error(mysqli_error($dbc), E_USER_ERROR);

                    // Get ingredient ID
                    $query_two = "SELECT `ingredient_id` FROM ingredient WHERE recipe_id = ?";

                    $results_two = parameterizedQuery($dbc, $query_two, 'i',
                            $id_to_edit)
                            or trigger_error(mysqli_error($dbc), E_USER_ERROR);

                    for ($index2 = 0; $index2 < count($ingredient_names); $index2++)
                    {
                            $row_two = mysqli_fetch_assoc($results_two);
                            $ingredient_id = $row_two['ingredient_id'];

                            $query_three = "UPDATE ingredient SET `ingredient` = ?, "
                            . "`percentage` = ? WHERE recipe_id = ? AND ingredient_id = ?";
                            
                            $results_three = parameterizedQuery($dbc, $query_three, 'sdii',
                                $ingredient_names[$index2], $percentages[$index2], $id_to_edit, $ingredient_id)
                                or trigger_error(mysqli_error($dbc), E_USER_ERROR);
                    }                    

                    $nav_link = 'viewrecipe.php?id=' . $id_to_edit;
                    header("Location: $nav_link");
                    exit;

                    }
                    else
                    {
                        echo "<h5><p class='text-danger'>" . $file_error_message . "</p></h5>";
                        return;
                    }
            
            } else { // Unintended page link - No user to edit, link back to index
                header("Location: unauthorizedaccess.php");
                exit;
            }
            ?>
            <!-- Display sanitized data in the edit form -->      
            <div class="row">
                <div class="col">
                    <form enctype="multipart/form-data" class="needs-validation" novalidate method="POST"
                            action="<?= $_SERVER['PHP_SELF'] ?>">
                        <!-- Recipe Title  -->
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="recipe_title"
                                    class="col-form-label-lg">Title</label>
                                <input type="text" class="form-control" id="recipe_title"
                                    name ="recipe_title" placeholder="Name" value="<?= $title ?>" required>
                                <div class="invalid-feedback">
                                    Please provide a valid title.
                                </div>
                            </div>
                        </div>
                        <!-- Description  -->
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="recipe_description"
                                    class="col-form-label-lg">Description</label>
                                <textarea class="form-control" id="recipe_description" name ="recipe_description"
                                    required style="height: 100px"><?= $description ?></textarea>
                                <div class="invalid-feedback">
                                    Please provide a valid description.
                                </div>
                            </div>
                            <div class="col-2 d-flex justify-content-end">
                                <img src="<?= $recipe_image_file ?>" class="img-thumbnail"
                                    style="max-height: 150px;" alt="Recipe post image">
                            </div>
                        </div>
                    <!-- Instructions -->
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="recipe_instructions"
                                class="col-form-label-lg">Instructions</label>
                            <textarea class="form-control" id="mytextarea" name ="recipe_instructions"
                                placeholder="Write post here" required style="height: 100px"><?= $instructions ?></textarea>
                            <div class="invalid-feedback">
                                Please provide valid instructions.
                            </div>
                        </div>
                    </div>
                    <!-- Add image -->
                    <div class="form-row">
                        <label for="recipe_image_file"
                            class="col-sm-3 col-form-label-lg">Recipe Image File</label>
                        <input type="file" class="form-control-file"
                            id="recipe_image_file" name="recipe_image_file">
                    </div>
                    <br/>
                    <br/>
                    <hr/>
                    <!-- Ingredients -->
                    <h2>Ingredients</h2>
                    <?php

                        // Initialize Ingredient Counter
                        $index = 1;

                        while ($row_two = mysqli_fetch_assoc($results_two))
                        {
                            $ingredient = htmlspecialchars($row_two['ingredient']);
                            $percentage = htmlspecialchars($row_two['percentage']);

                            echo 
                            "<div class='form-row'>
                                <div class='form-group col-md-3'>
                                    <label for='ingredient_name_$index'
                                        class='col-form-label-lg'>Ingredient $index Name</label>
                                    <input type='text' class='form-control' id='ingredient_name_$index'
                                        name ='ingredient_name[]' placeholder='Name' value ='$ingredient' required>
                                    <div class='invalid-feedback'>
                                        Please provide a valid ingredient.
                                    </div>
                                </div>                    

                                <div class='form-group col-md-2'>
                                    <label for='percentage_$index'
                                        class='col-form-label-lg'>Percentage</label>
                                    <input type='number' step='any' class='form-control' id='percentage_$index'
                                        name ='percentage[]' placeholder='Percentage' value='$percentage' required>
                                    <div class='invalid-feedback'>
                                        Please provide a valid percentage.
                                    </div>
                                </div>
                            </div>";

                            $index++;
                        }
                    ?>
                    
                    <!-- Button -->
                    <div class="form-row">
                        <button class="btn" style='background-color: #472eab; color: #ffffff;'
                                type="submit" name="edit_recipe_submission">Edit Recipe</button>
                        <input type="hidden" name="id_to_edit" value="<?= $id_to_edit ?>">
                        <input type="hidden" name="recipe_image_file" value="<?= $recipe_image_file ?>">                    
                    </div>
                </form>
            
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
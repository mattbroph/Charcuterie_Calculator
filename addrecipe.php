<?php
    require_once('authorizeaccess.php');
    require_once('pagetitles.php');
    $page_title = CR_ADD_RECIPE_PAGE;
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
        <link rel="stylesheet"
          href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
          integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf"
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
                    // If form was submitted and session variables = admin
                    if (isset($_POST['add_recipe_submission']))
                    {
                        require_once('dbconnection.php');
                        require_once('queryutils.php');
                        require_once('recipeimagefileutil.php');
                        require_once('recipefileconstants.php');

                        $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
                        or trigger_error(
                            'Error connecting to MySQL server for' . DB_NAME,
                            E-USER_ERROR
                        );

                        $recipe_title = $_POST['recipe_title'];
                        $recipe_description = $_POST['recipe_description'];
                        $ingredient_names = $_POST['ingredient_name'];
                        $percentages = $_POST['percentage'];
                        $instructions = $_POST['recipe_instructions'];
                        // Image
                        $recipe_image_file = isset($_POST['recipe_image_file']) ? $_POST['recipe_image_file'] : '';
                        
                        // Image
                        // If recipe image file is empty, set it to the default image file
                        // Otherwise, assign it to the local variable
                        if (empty($recipe_image_file))
                        {
                            $recipe_image_file = CR_UPLOAD_PATH . CR_DEFAULT_RECIPE_FILE_NAME;
                        }
                        else
                        {
                            $recipe_image_file = $_POST['recipe_image_file'];
                        }

                        // Image
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

                            // Insert into recipe table and obtain the recipe_id
                            $query = "INSERT INTO recipe (`title`, `description`, `image_file`, `instructions`) VALUES (?, ?, ?, ?)";

                            $results = parameterizedQuery($dbc, $query, 'ssss', $recipe_title, $recipe_description, $recipe_image_file, $instructions)
                                    or trigger_error(mysqli_error($dbc), E_USER_ERROR);

                            $recipe_id = mysqli_insert_id($dbc);

                            // Insert ingredients into ingredient table
                            for ($index = 0; $index < count($ingredient_names); $index++)
                            {
                                if (!empty($ingredient_names[$index])
                                        && !empty($percentages[$index]))
                                {
                                    $query = "INSERT INTO ingredient (`recipe_id` , `ingredient`, "
                                            . " `percentage`) VALUES (?, ?, ?)";

                                    $results = parameterizedQuery($dbc, $query, 'isd', $recipe_id,
                                            $ingredient_names[$index], $percentages[$index])
                                            or trigger_error(mysqli_error($dbc), E_USER_ERROR);
                                }
                            }

                            header("Location: index.php");
                            exit;
                        }
                        else
                        {
                            echo "<h5><p class='text-danger'>" . $file_error_message . "</p></h5>";
                            return;
                        }
                    }
                ?>
                <!-- Add Recipe Form -->
                <form enctype="multipart/form-data" class="needs-validation" novalidate method="POST"
                    action="<?= $_SERVER['PHP_SELF'] ?>">

                    <!-- Recipe Details -->

                    <!-- Recipe Title  -->
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="recipe_title"
                                class="col-form-label-lg">Title</label>
                            <input type="text" class="form-control" id="recipe_title"
                                name ="recipe_title" placeholder="Name" required>
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
                                placeholder="Write post here" required style="height: 100px"></textarea>
                            <div class="invalid-feedback">
                                Please provide a valid description.
                            </div>
                        </div>
                    </div>
                    <!-- Instructions -->
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="recipe_instructions"
                                class="col-form-label-lg">Instructions</label>
                            <textarea class="form-control" id="mytextarea" name ="recipe_instructions"
                                placeholder="Write post here" required style="height: 100px"></textarea>
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
                    <hr/>

                    <!-- Recipe Ingredients  -->

                    <div class="form-row">
                        <!-- Ingredient 1 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_one"
                                class="col-form-label-lg">Ingredient 1 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_one"
                                name ="ingredient_name[]" placeholder="Name" required>
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 1 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_one"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_one"
                                name ="percentage[]" placeholder="Percentage" required>
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>                    
                    <div class="form-row">
                        <!-- Ingredient 2 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_two"
                                class="col-form-label-lg">Ingredient 2 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_two"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 2 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_two"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_two"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <!-- Ingredient 3 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_three"
                                class="col-form-label-lg">Ingredient 3 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_three"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 3 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_three"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_three"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>                    
                    <div class="form-row">
                        <!-- Ingredient 4 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_four"
                                class="col-form-label-lg">Ingredient 4 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_four"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 4 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_four"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_four"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <!-- Ingredient 5 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_five"
                                class="col-form-label-lg">Ingredient 5 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_five"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 5 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_five"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_five"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>                    
                    <div class="form-row">
                        <!-- Ingredient 6 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_six"
                                class="col-form-label-lg">Ingredient 6 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_six"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 6 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_six"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_six"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <!-- Ingredient 7 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_seven"
                                class="col-form-label-lg">Ingredient 7 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_seven"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 7 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_seven"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_seven"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>                    
                    <div class="form-row">
                        <!-- Ingredient 8 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_eight"
                                class="col-form-label-lg">Ingredient 8 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_eight"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 8 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_eight"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_eight"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <!-- Ingredient 9 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_nine"
                                class="col-form-label-lg">Ingredient 9 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_nine"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 9 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_nine"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_nine"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>                    
                    <div class="form-row">
                        <!-- Ingredient 10 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_ten"
                                class="col-form-label-lg">Ingredient 10 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_ten"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 10 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_ten"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_ten"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <!-- Ingredient 11 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_eleven"
                                class="col-form-label-lg">Ingredient 11 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_eleven"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 11 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_eleven"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_eleven"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>                    
                    <div class="form-row">
                        <!-- Ingredient 12 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_twelve"
                                class="col-form-label-lg">Ingredient 12 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_twelve"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 12 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_twelve"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_twelve"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <!-- Ingredient 13 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_thirteen"
                                class="col-form-label-lg">Ingredient 13 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_thirteen"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 13 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_thirteen"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_thirteen"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <!-- Ingredient 14 Name -->
                        <div class="form-group col-md-3">
                            <label for="ingredient_name_fourteen"
                                class="col-form-label-lg">Ingredient 14 Name</label>
                            <input type="text" class="form-control" id="ingredient_name_fourteen"
                                name ="ingredient_name[]" placeholder="Name">
                            <div class="invalid-feedback">
                                Please provide a valid ingredient.
                            </div>
                        </div>                    
                    <!-- Ingredient 14 Percentage -->
                        <div class="form-group col-md-2">
                            <label for="percentage_fourteen"
                                class="col-form-label-lg">Percentage</label>
                            <input type="number" step="any" class="form-control" id="percentage_fourteen"
                                name ="percentage[]" placeholder="Percentage">
                            <div class="invalid-feedback">
                                Please provide a valid percentage.
                            </div>
                        </div>
                    </div>
                    <br/>
                    <br/>
                    <!-- Button -->
                    <button class='btn' style='background-color: #472eab; color: #ffffff;' type="submit"
                            name="add_recipe_submission">Add Recipe</button>
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
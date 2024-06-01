
<?php
    require_once 'recipefileconstants.php';

    /** Purpose: Validates an uploaded recipe image file
     * 
     * Description: Validates an uploaded recipe image file is not great than CR_MAX_FILE_SIZE (1/2 MB),
     * and is either a jpg or png image type, and has no errors. If the image file validates
     * to these constraints, an error message containing an empty string is 
     * returned. If there is an error, a string containing the constraints the file failed to 
     * validate to are returned.
     * 
     * @return string Empty if validation is succesful, otherwise error string containging
     * constraints the image file failed to valide to.
     */

     function validateRecipeImageFile()
     {
        $error_message = "";

        // Check for $_FILES being set and no errors.
        if (isset($_FILES) && $_FILES['recipe_image_file']['error'] == UPLOAD_ERR_OK)
        {
            // Check for uploaded file < Max file size AND an acceptable image type
            if (isset($_FILES) && $_FILES['recipe_image_file']['size'] > CR_MAX_FILE_SIZE)
            {
                $error_message = "The recipe file image must be less than " . CR_MAX_FILE_SIZE
                        . " Bytes";
            }

            $image_type = $_FILES['recipe_image_file']['type'];

            $allowed_image_types = [
                    'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png'
                    , 'image/gif'];

            // if ($image_type != 'image/jpg' && $image_type != 'image/jpeg'
            //          && $image_type != 'image/pjpeg' && $image_type != 'image/png'
            //          && $image_type != 'image/gif')
            // {
            if (!in_array($image_type, $allowed_image_types))
            {            
                if (empty($error_message))
                {
                    $error_message = "The recipe file image must be of type jpg, png or gif.";
                }
                else
                {
                    $error_message .=", and be an image of type jpg, png or gif.";
                }
            }            
        }
        else if (isset($_FILES) && $_FILES['recipe_image_file']['error'] != UPLOAD_ERR_NO_FILE
                && $_FILES['recipe_image_file']['error'] != UPLOAD_ERR_OK)
        {
            $error_message = "Error uploading recipe image file.";
        }

        return $error_message;
     }




    /**
     * Purpose: Moves an uploaded recipe image file to the CR_UPLOAD_PATH (images/)
     * folder and return the path location
     * 
     * Description: Moves an uploaded recipe image file form the temporary server location
     * to the CR_UPLOAD_PATH (images/) folder IF a recipe image file was uploaded
     * and returns the path location of the uploaded file be appending the file
     * name to the CR_UPLOADPATH (e.g. images/recipe_image.png). IF a recipe image
     * file was NOT uploaded, an empty string will be returned for the path.
     * 
     * @return string Path to the recipe image file IF a file was uplaoded AND moved to 
     * CR_UPLOAD_PATH (images/) folder, otherwise and empty string.
     *  
     */

    function addRecipeImageFileReturnPathLocation()
    {
        $recipe_file_path = "";

        // Check for files being set and no errors
        if (isset($_FILES) && $_FILES['recipe_image_file']['error'] == UPLOAD_ERR_OK)
        {
            $recipe_file_path = CR_UPLOAD_PATH . $_FILES['recipe_image_file']['name'];

            if (!move_uploaded_file($_FILES['recipe_image_file']['tmp_name'], $recipe_file_path))
                $recipe_file_path = "";
        }

        return $recipe_file_path;
    }



    /**
    * Purpose: Removes a file given a path to that file.
    *
    * Description: Removes the file referenced by $recipe_file_path. Supresses error
    * if file cannot be removed.
    *
    * @param $recipe_file_path
    */

    function removeRecipeImageFile($recipe_file_path)
{
    @unlink($recipe_file_path);
}






?>
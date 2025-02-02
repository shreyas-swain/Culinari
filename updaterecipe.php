<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
<link rel="stylesheet" href="style.css">
        
    <title>Culinari</title>
     
</head>
<body>

<?php 
// connecting to the server
require_once "config.php";

//  refering to application constants
require_once "appvars.php";
    
// Initialize the session
session_start();
    
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
    
$userAdding = $_SESSION['userID']; //variable to define the active user 
    
//to get the names of all recipes added by the active user to the list for updating
$sqlDelete = "SELECT * FROM recipe WHERE userID = '$userAdding' ORDER BY RecipeName;"; 
$result = mysqli_query($conn, $sqlDelete);


// define variables and set to empty values
$dishNameErr = $ServingsErr = $CookingTimeErr = $ingredientsErr = $instructionsErr = "";
$dishName = $Servings = $CookingTime = $rating = $ingredients = $instructions = "";

// input validation function
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
// checking if fields are left empty and if required to fill out, the message is displayed
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["dishName"])) {
      $dishNameErr = "Dish name is required";
    } else {
      $dishName = test_input($_POST["dishName"]);
      // check if name only contains letters and whitespace
      if (!preg_match("/^[a-zA-Z-' ]*$/",$dishName)) {
        $dishNameErr = "Only letters and white space allowed";
      }
    }

    if (empty($_POST["Servings"])) {
        $Servings = "";
    } else {
        $Servings = test_input($_POST["Servings"]);
      //   check if servings is a number or a numeric string
        if(!is_numeric($Servings)) {
            $ServingsErr = "Only numbers are allowed";
        }
      }
    
    if (empty($_POST["CookingTime"])) {
        $CookingTime = "Cooking time is required";
    } else {
        $CookingTime = test_input($_POST["CookingTime"]);
        //   check if CookingTime is a number or a numeric string
        if(!is_numeric($CookingTime)) {
          $CookingTimeErr = "Only numbers are allowed";
          }
      }

    if (empty($_POST["rating"])) {
         $rating = "";
     } else {
         $rating = test_input($_POST["rating"]);
         }
     
    
    if (empty($_POST["ingredients"])) {
        $ingredientsErr = "Ingredients are required";
    } else {
        $ingredients = test_input($_POST["ingredients"]);
        }
        
        
    if (empty($_POST["instructions"])) {
        $instructionsErr = "Instructions are required";
    } else {
        $instructions = test_input($_POST["instructions"]);
        }
      
     //   adding photos, first setting variables

// File upload path
$targetDir = RECIPE_UPLOAD_PATH;
$fileName = basename($_FILES["addPhoto"]["name"]);
$targetFilePath = RECIPE_UPLOAD_PATH . $fileName;
$fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);


    // Allow certain file formats
    $allowTypes = array('jpg','png','jpeg','gif');
    if(in_array($fileType, $allowTypes)){
        // Upload file to server
        if(move_uploaded_file($_FILES["addPhoto"]["tmp_name"], $targetFilePath)){
            // Insert image file name into database
           $insert = $conn->query("INSERT into recipe (Images) VALUES ('".$fileName."')");
            if($insert){
                $statusMsg = "The file ".$fileName. " has been uploaded successfully.";
            }else{
                $statusMsg = "File upload failed, please try again.";
            } 
        }else{
            $statusMsg = "Sorry, there was an error uploading your file.";
        }
    }else{
        $statusMsg = 'Sorry, only JPG, JPEG, PNG, GIF files are allowed to upload.';
    }
}

//  sql to update records from recipe table
$sql = "UPDATE recipe SET Servings ='$Servings', PreparationTime ='$CookingTime', Ratings = '$rating', Ingredients ='$ingredients', Instructions ='$instructions', Images ='".$fileName."' WHERE RecipeName = '$dishName'";

if (($conn->query($sql) === TRUE) && ($_SERVER["REQUEST_METHOD"] == "POST")) {
    header( "location: successfullyupdated.php" );  //if the data is successfully updated to database after submitting, user will be redirected
    exit;
 } else {
   echo "";
 }
  
  
$conn->close(); //close the connection
   
?>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light">
  <a class="navbar-brand">RecipeBook</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggle" aria-controls="navbarToggle" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse justify-content-center" id="navbarToggle">
    <ul class="navbar-nav">
       <li class="nav-item">
        <a class="nav-link" href="drinks.php">Drinks</a>
       </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Manage Recipes
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="addnewrecipe.php">Add</a>
          <a class="dropdown-item" href="updaterecipe.php">Update</a>
          <a class="dropdown-item" href="deleterecipe.php">Delete</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="ownrecipes.php">Own Recipes</a>
        </div>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-person-circle" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M13.468 12.37C12.758 11.226 11.195 10 8 10s-4.757 1.225-5.468 2.37A6.987 6.987 0 0 0 8 15a6.987 6.987 0 0 0 5.468-2.63z"/>
            <path fill-rule="evenodd" d="M8 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
            <path fill-rule="evenodd" d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8z"/>
         </svg> 
         <?php  if (isset($_SESSION['Username'])) : ?><?php echo $_SESSION['Username']; ?><?php endif ?>  <!-- show the active user in navbar -->
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="logout.php">Logout</a>
        </div>
      </li>
    </ul>
  </div>
</nav>


<!-- Form -->
<div class="container-fluid">
   <div class="row justify-content-around">
      <div class="col-lg-6">
         <h4>Update your recipe</h4>
      </div>
   </div>
   <div class="row justify-content-around">
      <div class="col-lg-6">
      <!-- This enctype attribute tells that we will be adding files like images -->
         <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" enctype="multipart/form-data"> <!-- returns the filename of the currently executing script and converts special characters to HTML entities -->
            <div class="form-group">
               <div class="row">
               <div class="col">
                      <label for="addPhoto">Change a photo</label> 
                  </div>
                  <div class="col">
                     <input type="file" class="form-control-file" id="addPhoto" name="addPhoto">
                  </div>
                </div>
            </div>
            <div class="form-group">
               <label>Name of the dish</label>
               <select class="form-control"  name="dishName" id="dishName"><option selected disabled>Choose the recipe</option>
                    <?php while ($row = mysqli_fetch_array($result)){ ?>
                           <option><?php echo $row ['RecipeName']; ?></option>
                    <?php } ?>  <!-- displays in a list all the recipes added by the active user -->
               </select>
            </div>              
            <div class="form-group">
               <div class="row">
                  <div class="col">
                  <label>Change number of servings</label>
                  </div>
                  <div class="col">
                  <label>Change cooking time (min)</label>
                  </div>
                  <div class="col">
                  <label>Change rating</label>
                  </div>
               </div>
               <div class="row">
                  <div class="col">
                  <input type="text" class="form-control" name="Servings" value="<?php echo $Servings;?>">  <!-- update the user input to the database -->
                  <span class="error">* <?php echo $ServingsErr;?></span> <!-- show error message -->
                  </div>
                  <div class="col">
                  <input type="text" class="form-control" name="CookingTime" value="<?php echo $CookingTime;?>">  <!-- update the user input to the database -->
                  <span class="error">* <?php echo $CookingTimeErr;?></span> <!-- show error message -->
                  </div>
                  <div class="col">
                  <select class="form-control"  id="rating" name="rating">
                        <option selected>Rating</option>
                        <option>1</option>
                        <option>2</option>
                        <option>3</option>
                        <option>4</option>
                        <option>5</option>
                     </select>
                  </div>
               </div>
            </div>
            <div class="form-group">
               <label for="exampleFormControlTextarea1">Update ingredients</label>
               <textarea class="form-control" name="ingredients" id="ingredients" rows="3" value="<?php echo $ingredients;?>"></textarea>  <!-- update the user input to the database -->
            </div>
            <div class="form-group">
               <label for="exampleFormControlTextarea1">Update instructions</label>
               <textarea class="form-control" name="instructions" id="instructions" rows="3" value="<?php echo $instructions;?>"></textarea>  <!-- update the user input to the database -->
            </div>
            <button type="submit" class="btn btn-primary" name="submit" value="Submit">Update recipe</button>
         </form>
         <br>
      </div>
   </div>
</div>
    

<!-- Footer -->
<footer class="footer text-center">
    <div class="container">
          <small>Copyright &copy; Shreyas Swain 
         <?php
            echo date ('Y');  // Get the current year
            ?>
         Culinari</small>
    </div>
</footer>
    
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>
<?php
    $page_title = isset($page_title) ? $page_title : "";

    if (session_status() == PHP_SESSION_NONE)
    {
        session_start();
    }
?>
<nav class="navbar sticky-top navbar-expand-md navbar-dark"
     style="background-color: #472eab">
    <a class="navbar-brand" href=<?= dirname($_SERVER['PHP_SELF']) ?>>
        <img src="resources/logo.png" width="30" height="30"
             class="d-inline-block align-top" alt="">
        <?= CR_HOME_PAGE ?>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse"
            data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup"
            aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav">
            <a class="nav-item nav-link<?= $page_title == CR_HOME_PAGE ? ' active' : '' ?>"
               href=<?= dirname($_SERVER['PHP_SELF']) ?>>Home</a>
        <?php if(isset($_SESSION['user_access_privileges'])
                && $_SESSION['user_access_privileges'] == 'admin'): ?>
            <a class="nav-item nav-link<?= $page_title == CR_ADD_RECIPE_PAGE ? ' active' : '' ?>"
               href="addrecipe.php">Add a Recipe</a>
        <?php endif; ?>
        <?php if (!isset($_SESSION['user_name'])): ?>
            <a class="nav-item nav-link<?= $page_title == CR_LOGIN_PAGE ? ' active' : '' ?>"
               href="login.php">Admin Login
               <img src="resources/locked.png" width="18" height="18"
                    class="d-inline-block align-top" alt="">
            </a>
        <?php else: ?>
            <a class='nav-item nav-link'
                    href='logout.php'>Logout (<?= $_SESSION['user_name'] ?>)
                <img src="resources/unlocked.png" width="18" height="18"
                    class="d-inline-block align-top" alt="">            
            </a>
        <?php endif; ?> 

        </div>
    </div>
</nav>

<?php require "view_begin.php"; ?>

<link rel="stylesheet" href="Content/css/auth.css"/>

<nav>
    <div class="container" id="container">
        <div class="form-container sign-in-container">
            <form action="?controller=auth&action=change_mdp" method="post">
                <h1>Changer de mot de passe</h1>
                <input type="email" name="email" placeholder="Email" required/> </form><br>
                <input type="submit" value="validée" class="blanc button" />
            </form>
        </div>
    </div>
</nav>

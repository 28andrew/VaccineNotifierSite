<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a id="navbar-logo" class="navbar-brand text-light" href="/">Vaccine Notifier</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link<?=$active == 'home' ? ' active' : ''?>" href="/">Home</a>
                </li>
                <?php
                if (isset($_USER)) {
                    ?>
                    <li class="nav-item">
                        <a class="nav-link<?=$active == 'dashboard' ? ' active' : ''?>" href="/dashboard/">Dashboard</a>
                    </li>
                    <?php
                }
                ?>
                <li class="nav-item">
                    <a class="nav-link<?=$active == 'about' ? ' active' : ''?>" href="/about/">About</a>
                </li>
            </ul>
            <?php
            if (!$hideTopRight) {
                if (!isset($_USER)) {
                    ?>
                    <div class="btn-group" role="group" aria-label="Account Buttons">
                        <a class="btn btn-outline btn-success registration-button" href="/login">Login</a>
                        <a class="btn btn-outline btn-secondary registration-button" href="/register">Register</a>
                    </div>
                    <?php
                } else {
                    ?>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="nav-item dropdown active">
                            <a class="nav-link dropdown-toggle" href="#" id="navbar-drop" data-toggle="dropdown">
                                <?= $_USER->email ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="/logout.php">Logout</a>
                            </div>
                        </li>
                    </ul>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</nav>
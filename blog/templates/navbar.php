<nav>
    <div class="nav-wrapper teal">
        <span class="brand-logo center">Suso-Blog</span>
        <a href="#" data-activates="slide-out" class="button-collapse">
            <i class="material-icons">menu</i>
        </a>
        <ul class="right hide-on-med-and-down">
            <li class="loginbtn hidden">
                <a href="#login"><span>Login</span><i class="material-icons right">person</i></a>
            </li>
            <li class="logoutbtn hidden">
                <a href="javascript:void(0);" onclick="logout();"><span>Logout</span><i class="material-icons right">power_settings_new</i></a>
            </li>
        </ul>
        <ul class="left hide-on-med-and-down">
            <li>
                <a id="home" href=".." title="Home" class="waves-effect"><i class="material-icons left">home</i> <font style="font-size: 24px">Suso-Intern</font></a>
            </li>
        </ul>
    </div>
    <ul id="slide-out" class="side-nav" style="z-index:999;">
        <li>
            <img class="logo-mobile" src="../assets/logo.png" alt="Logo">
        </li>
        <li>
            <a id="home" href=".." title="Home" class="waves-effect"><i class="material-icons left">arrow_back</i> <font style="font-size: 24px">Suso-Intern</font></a>
        </li>
        <li class="loginbtn hidden">
            <a href="#login"><span>Login</span><i class="material-icons left">person</i></a>
        </li>
        <li class="logoutbtn hidden">
            <a href="javascript:void(0);" onclick="logout();"><span>Logout</span><i class="material-icons left">power_settings_new</i></a>
        </li>
    </ul>

</nav>
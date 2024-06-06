<nav class="navbar navbar-expand navbar-light navbar-bg    ">
    <div class="d-flex align-items-center justify-content-between col-12">
        <div>
            <a class="sidebar-toggle js-sidebar-toggle">
                <i class="hamburger align-self-center"></i>
            </a>
        </div>
        <div>
            <a style="text-decoration: none" class="link " href="{{ route('logout') }}">
                <span>Log out</span>
                <span class="align-middle"><i class="fas fa-sign-out-alt"></i></span>
            </a>


        </div>
    </div>

 

{{-- <div class="navbar-collapse collapse">
    <ul class="navbar-nav navbar-align">
        <li class="nav-item dropdown">
            <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" role="button" data-bs-toggle="dropdown"  aria-expanded="false">
                <i class="align-middle" data-feather="settings"></i>
            </a>
            <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ asset('assets/img/avatars/avatar.jpg') }}" class="avatar img-fluid rounded me-1" alt="Charles Hall" /> <span class="text-dark">Super Admin</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="pages-profile.html"><i class="align-middle me-1" data-feather="user"></i> Profile</a>
                <a class="dropdown-item" href="#"><i class="align-middle me-1" data-feather="pie-chart"></i> Analytics</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="index.html"><i class="align-middle me-1" data-feather="settings"></i> Settings & Privacy</a>
                <a class="dropdown-item" href="#"><i class="align-middle me-1" data-feather="help-circle"></i> Help Center</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('logout') }}">Log out</a>
            </div>
        </li>
    </ul>
</div> --}}



</nav>

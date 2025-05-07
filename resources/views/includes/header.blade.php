<nav class="navbar navbar-expand navbar-light bg-white sticky-top">
    <a class="sidebar-toggle d-flex mr-3">
        <i class="align-self-center" data-feather="menu"></i>
    </a>

    <form class="form-inline d-none d-sm-inline-block">
        <input class="form-control form-control-no-border navbar-search mr-sm-2" type="text" placeholder="Search topics..." aria-label="Search">
    </form>

    <div class="navbar-collapse collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-icon dropdown-toggle ml-2 d-inline-block d-sm-none" href="#" id="userDropdown" data-toggle="dropdown">
                    <div class="position-relative">
                        <i class="align-middle mt-n1" data-feather="settings"></i>
                    </div>
                </a>
                <a class="nav-link nav-link-user dropdown-toggle d-none d-sm-inline-block" href="#" id="userDropdown" data-toggle="dropdown">
                    <img src="{{ asset('assets/images/avatar-5.png') }}" class="avatar img-fluid rounded mr-1" alt="Kathy Davis" /> <span class="text-dark">{{ auth()->user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <form id="logout-form" method="POST" action="{{ url('logout') }}">@csrf</form>
                    <a class="dropdown-item " onclick="document.getElementById('logout-form').submit();" style="cursor: pointer">Sign out</a>
                </div>
            </li>

        </ul>
    </div>
</nav>


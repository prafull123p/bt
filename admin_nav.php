<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <!-- Navbar brand -->
        <a class="navbar-brand" href="admin.php">Admin Panel</a>

        <!-- Toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible wrapper -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left links -->
            <ul class="navbar-nav me-auto d-flex flex-row mt-3 mt-lg-0">
                <li class="nav-item text-center mx-2 mx-lg-1">
                    <a class="nav-link active" aria-current="page" href="admin.php">
                        <div>
                            <i class="bi bi-speedometer2 fa-lg mb-1"></i>
                        </div>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item text-center mx-2 mx-lg-1">
                    <a class="nav-link" href="admin_staff.php">
                        <div>
                            <i class="bi bi-journal-text fa-lg mb-1"></i>
                        </div>
                        Staff
                    </a>
                </li>
                <li class="nav-item text-center mx-2 mx-lg-1">
                    <a class="nav-link" href="addimgfor_slide.php">
                        <div>
                            <i class="bi bi-plus-square fa-lg mb-1"></i>
                        </div>
                        Add Slide
                    </a>
                </li>
                <li class="nav-item text-center mx-2 mx-lg-1">
                    <a class="nav-link" href="admin_notification.php">
                        <div>
                            <i class="bi bi-bell fa-lg mb-1"></i>
                        </div>
                        Notification
                    </a>
                </li>
                <li class="nav-item text-center mx-2 mx-lg-1">
                    <a class="nav-link" href="admin_blog.php">
                        <div>
                            <i class="bi bi-journal-text fa-lg mb-1"></i>
                        </div>
                        Blog
                    </a>
                </li>
                <li class="nav-item text-center mx-2 mx-lg-1">
                    <a class="nav-link" href="admin_events.php">
                        <div>
                            <i class="bi bi-calendar-event fa-lg mb-1"></i>
                        </div>
                        Event
                    </a>
                </li>
                <li class="nav-item text-center mx-2 mx-lg-1">
                    <a class="nav-link" href="admin.php?export=csv">
                        <div>
                            <i class="bi bi-file-earmark-arrow-down fa-lg mb-1"></i>
                        </div>
                        Export Slides
                    </a>
                </li>
                <li class="nav-item text-center mx-2 mx-lg-1">
                    <a class="nav-link" href="logout.php">
                        <div>
                            <i class="bi bi-box-arrow-right fa-lg mb-1"></i>
                        </div>
                        Logout
                    </a>
                </li>
            </ul>
            <!-- Search form -->
            <form class="d-flex input-group w-auto ms-lg-3 my-3 my-lg-0" method="get" action="admin.php">
                <input type="search" name="search" class="form-control" placeholder="Search" aria-label="Search" />
                <button class="btn btn-primary" type="submit">
                    Search
                </button>
            </form>
        </div>
        <!-- Collapsible wrapper -->
    </div>
    <!-- Container wrapper -->
</nav>
<!-- Navbar -->
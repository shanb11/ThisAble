<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Jobs - ThisAble</title>
        <link rel="stylesheet" href="../../styles/landing/jobs.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>

    <body>
        <!-- Navigation Bar -->
        <?php include('../../includes/landing/landing_navbar.php'); ?>

        <!-- Page Header -->
        <?php include('../../includes/landing/landing_jobs_header.php'); ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <!-- Job Search Section -->
                <?php include('../../includes/landing/landing_jobs_jobsearch.php'); ?>

                <!-- Jobs Grid -->
                <section class="jobs-grid" id="jobs-grid">
                    <!-- Jobs will be loaded here via JavaScript -->
                </section>

               

                <!-- Featured Categories Section -->
                <?php include('../../includes/landing/landing_jobs_featured.php'); ?>

                <!-- Job Alert Section -->
                <?php include('../../includes/landing/landing_jobs_jobalert.php'); ?>

            </div>
        </main>

        <!-- Footer -->
        <?php include('../../includes/landing/landing_footer.php'); ?>

        <!-- Job Details Modal -->
        <?php include('../../modals/landing/landing_job_jobdetails.php'); ?>

        <!-- Post Job Modal -->
        <?php include('../../modals/landing/landing_job_post_job_modal.php'); ?>

        <script src="../../scripts/landing/job.js"></script>
    </body>
</html>

<div class="sidebar">
            <div class="sidebar-header">
                <h2>Course Dashboard</h2>
            </div>
            <div class="menu-list">
                
                <div class="menu-item" data-page="dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <a href="/projectcoder/clgproject/menus/programme_code.php"><div class="menu-item" data-page="programme_code">
                    <i class="fas fa-barcode"></i>
                    <span>Programme Code</span>
                </div></a>
                <a href="/projectcoder/clgproject/menus/preamble.php"><div class="menu-item" data-page="preamble">
                    <i class="fas fa-file-signature"></i>
                    <span>Preamble</span>
                </div></a>
                <a href="/projectcoder/clgproject/menus/prerequisite.php"> <div class="menu-item" data-page="pre_requisite">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Pre Requisite</span>
                </div></a>
                <a href="/projectcoder/clgproject/menus/courseoutcomes.php">
                    <div class="menu-item" data-page="course_outcomes">
                    <i class="fas fa-bullseye"></i>
                    <span>Course Outcomes</span>
                </div></a>
                <a href="/projectcoder/clgproject/menus/mapping_of_cos_with_pos.php">
                    <div class="menu-item" data-page="mapping_cos_pos">
                    <i class="fas fa-sitemap"></i>
                    <span>Mapping COs With POs</span>
                </div></a>
                <div class="menu-item" data-page="mapping_cos_psos">
                    <i class="fas fa-network-wired"></i>
                    <span>Mapping COs with PSOs</span>
                </div>
                <div class="menu-item" data-page="blooms_taxonomy">
                    <i class="fas fa-seedling"></i>
                    <span>Blooms Taxonomy</span>
                </div>
                <div class="menu-item" data-page="content">
                    <i class="fas fa-book-open"></i>
                    <span>Content</span>
                </div>
                <div class="menu-item" data-page="chapters">
                    <i class="fas fa-list-ol"></i>
                    <span>Chapters</span>
                </div>
                <div class="menu-item" data-page="text_book">
                    <i class="fas fa-book"></i>
                    <span>Text Book</span>
                </div>
                <div class="menu-item" data-page="reference_book">
                    <i class="fas fa-book-reader"></i>
                    <span>Reference Book</span>
                </div>
                <div class="menu-item" data-page="web_resources">
                    <i class="fas fa-globe"></i>
                    <span>Web Resources</span>
                </div>
                <div class="menu-item" data-page="course_designers">
                    <i class="fas fa-users-cog"></i>
                    <span>Course Designers</span>
                </div>
                <div class="menu-item" data-page="department">
                    <i class="fas fa-university"></i>
                    <span>Department</span>
                </div>
                <div class="menu-item" data-page="download_pdf">
                    <i class="fas fa-file-pdf"></i>
                    <span>Download PDF</span>
                </div>
            </div>
        </div>


<script>
    function adjustZoom() {
        const screenWidth = window.innerWidth;
        let baseFontSize = 16; // Default font size in pixels

        if (screenWidth <= 1366) {
        baseFontSize = 11.6; // Decrease font size for smaller screens
        }

        document.documentElement.style.fontSize = `${baseFontSize}px`;
        }

        // Call function on page load and resize
        document.addEventListener("DOMContentLoaded", adjustZoom);
        window.addEventListener("resize", adjustZoom);

</script>
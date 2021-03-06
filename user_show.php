<?php
if (count(get_included_files()) == 1): exit("Access restricted.");
endif; // prevent direct access (include only)
// Connect to database
require 'scripts/db.php';

// Fetch user information from database
$usersql = "SELECT first_name AS fname, last_name AS lname, "
        . "email, phone, username AS uname, admin FROM users WHERE user_id = '$id'";
$user_result = mysqli_query($link, $usersql);

// Fetch information about entries from database
$entrysql = "SELECT entry.id AS eid, entry.comment, entry.year_created, entry.date_db, "
        . "entry.entry_reg, entry.private, entry_upstrain.upstrain_id AS uid, backbone.name AS bname, backbone.id AS bid, "
        . "strain.name AS sname, strain.id AS sid, entry_inserts.*, ins.name AS iname, ins.id AS iid FROM entry "
        . "LEFT JOIN entry_upstrain ON entry_upstrain.entry_id = entry.id "
        . "LEFT JOIN backbone ON entry.backbone = backbone.id "
        . "LEFT JOIN strain ON entry.strain = strain.id "
        . "LEFT JOIN entry_inserts ON entry_inserts.entry_id = entry.id "
        . "LEFT JOIN ins ON entry_inserts.insert_id = ins.id AND entry_inserts.entry_id = entry.id "
        . "WHERE entry.creator = '$id' "
        . "ORDER BY entry.id";
$entry_result = mysqli_query($link, $entrysql);

// Close database connection
mysqli_close($link) or die("Could not close database connection");

// Put user and first row of entry info in arrays
$info = mysqli_fetch_assoc($user_result);
$entry = mysqli_fetch_assoc($entry_result);
?>
<div class="user_page"><?php if ($loggedin && $active) { ?>
        <!-- Show user information -->
        <!-- Shows user as admin or user -->
        <?php
        if ($info["admin"] == 1)
            echo "<h2 class=\" user\">Admin ";
        else
            echo "<h2 class=\"user\">User ";
        echo $info["uname"] . "</h2>";
        ?>
        <br>
        <h3 class="user">Contact information</h3>
        <!-- <li class="user">
                <span class="user_title">Name:</span> <span class="user_info"> <?php echo $info["fname"] . " " . $info["lname"] ?></span>
        </li>
        <br>
        <li class="user">
                <span class="user_title">Email: </span> <span class="user_info"> <?php echo "<a class=\"mail\" href=\"mailto:" . $info["email"] . "\">" . $info["email"] . "</a>" ?></span>
        </li>
        <br>
        <li class="user">
        <span class="user_title">Phone number: </span> <span class="user_info"> <?php echo $info["phone"] ?></span>
        </li> -->
        <div class="user_title">
            Name: 
            <br>
            Email: 
            <br>
            Phone number: 
            <br>
        </div>
        <div class="user_info">
            <?php echo $info["fname"] . " " . $info["lname"] ?> 
            <br>
            <?php echo "<a class=\"mail\" href=\"mailto:" . $info["email"] . "\">" . $info["email"] . "</a>" ?>
            <br>
            <?php echo $info["phone"] ?> 
            <br>
        </div>
        <?php
    } else {
        ?>
        <div class="user_page">
            <h2 class="user">User profile</h2>
            <p>You need to be logged in and activated to see contact information.</p>
        </div>
    <?php } ?>
    <br>
    <?php
    // Decide if user can edit information
    if ($loggedin && $active && ($adminpage_owner || $userpage_owner_or_admin)) {
        ?>
        <div class="edit">
            <a class="edit" href="<?php echo $_SERVER['REQUEST_URI']; ?>&edit">Edit user information</a>
        </div>
    <?php } ?>
    <div class="clear"></div>
    <br>

    <!-- Show entry information -->
    <h3 class="user" style="font-style: normal; font-weight: 300; color: #001F3F;">User entries</h3>

    <?php if (mysqli_num_rows($entry_result) < 1) {
        ?>
        <div class="not_added">
            <p>User has not added any entries (yet).</p>
        </div>
    <?php } else {
        ?>

        <div style="display: block; margin-left:10%; margin-right: 10%;">
            <!-- Create table -->
            <table class="display" id="userentries">
                <thead>
                    <tr>
                        <th>Entry ID</th>
                        <th>Strain</th>
                        <th>Backbone</th>
                        <th>Inserts</th>
                        <th>Year created</th>
                        <th>iGEM Registry</th>
                        <th>Comment</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fill table with one entry at a time
                    while ($entry) {
                        if (($loggedin && $active) || $entry['private'] === '0') {
                            $current_entry = $entry["eid"];

                            // Part 1 of entry row with upstrain ID, strain and backbone
                            $tpart_1 = "<tr>"
                                    . "<td><a href=\"entry.php?upstrain_id=" . $entry["uid"] . "\">" . $entry["uid"] . "</a></td>"
                                    . "<td><a href=\"parts.php?strain_id=" . $entry['sid'] . "\">" . $entry["sname"] . "</a></td>"
                                    . "<td><a href=\"parts.php?backbone_id=" . $entry['bid'] . "\">" . $entry["bname"] . "</a></td>";

                            // Decide if user can edit entries
                            if ($admin) {
                                $edit = "<td>"
                                        . "<a class=\"edit\" href=\"entry.php?upstrain_id=" . $entry["uid"] . "&edit\">Edit</a></td>";
                            } else
                                $edit = "";

                            // Create biobrick registry link (or not)
                            if ($entry["entry_reg"] === null || $entry["entry_reg"] == '') {
                                $biobrick = "N/A";
                            } else {
                                $biobrick = "<a class=\"external\" href=\"http://parts.igem.org/Part:" . $entry["entry_reg"]
                                        . "\" target=\"_blank\">" . $entry["entry_reg"] . "</a>";
                            }

                            // Part 3 of entry row with year created, registry link, comment and edit link
                            $tpart_3 = "<td>" . $entry["year_created"] . "</td>"
                                    . "<td>" . $biobrick . "</td>"
                                    . "<td>" . $entry["comment"] . "</td>"
                                    . $edit
                                    . "</tr>";

                            // Part 2 of entry row, inserts (has to be created last since it cycles through the list of entries)
                            $inserts = "<a href=\"parts.php?ins_id=" . $entry["iid"] . "\">" . $entry["iname"] . "</a>";  // Grab first insert
                            $entry = mysqli_fetch_assoc($entry_result); // Go to next result in the list
                            while (TRUE) {
                                // Check if result is a different entry or end of results
                                if (!$entry || $entry["eid"] != $current_entry) {
                                    break;
                                }
                                // Add insert to list and go to next result
                                $inserts = $inserts . "<br><a href=\"parts.php?ins_id=" . $entry["iid"] . "\">" . $entry["iname"] . "</a>";
                                $entry = mysqli_fetch_assoc($entry_result);
                            }
                            $tpart_2 = "<td>" . $inserts . "</td>";

                            // Piece together the parts to form a row of the table
                            echo $tpart_1 . $tpart_2 . $tpart_3;
                        } else {
                            // If the entry is private, skip it unless user is logged in and activated
                            $entry = mysqli_fetch_assoc($entry_result);
                        }
                    }
                    ?>
                    <!-- End table -->
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
<script>
    $(document).ready(function () {
        $('#userentries').DataTable({
            paging: true,
            select: true,
            "order": [[4, "asc"]]
        });
    });
</script>
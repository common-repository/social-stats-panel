<?php
/*
  Plugin Name: Social Stats Panel
  Plugin URI: http://socialstatsplugin.com
  Description: See which of your posts have the most social signals shares! With the ability to sort and view in graph which is updated daily and showing the growth of your overall social signals across the 3 major platforms. Social Stats Plugin - Daily Social Signals Tracker!
  Version: 1.0.2
  Author: Jason Bailey
  Author URI: http://socialstatsplugin.com
 */

add_action('admin_menu', 'my_social_stats_graph');

function my_social_stats_graph() {
    add_menu_page('Social stats', 'Social stats', 'manage_options', 'social-stats-base', 'social_stats_graph_base', plugins_url('/images/icon.png', __FILE__));
    add_submenu_page('social-stats-base', 'Social stats detailed', 'Stats by posts', 'manage_options', 'social_stats_datailed', 'social_stats_graph');
    add_submenu_page('social-stats-base', 'Social stats pages', 'Stats by pages', 'manage_options', 'social_stats_pages', 'social_stats_pages');
    add_submenu_page('social-stats-base', "Update", "Update", "manage_options", 'social_stats_update', 'social_stats_update');
}

set_time_limit(0);
ini_set('max_execution_time', 0);

function social_stats_graph_base() {
    echo '<link rel="stylesheet" href="' . plugins_url('style.css', __FILE__) . '">';
    if (get_option('socLastUpdate') !== false) {
        //update_option('socLastUpdate', time());
    } else {
        add_option('socLastUpdate', time(), '', 'no');
    }
    if (get_option('NowSocUpdate') == 1) {
        echo '<div class="loader loaderTop" style="display: block">Statistics are updated now. Please check back in 30 minutes.</div>';
    }

    $data = array();

    $args = array(
        //'posts_per_page' => 100,
        'orderby' => 'date',
        'nopaging' => true,
        'order' => 'DESC',
        'post_status' => 'publish'
    );
    $q = new WP_Query($args);

    if ($q->have_posts()) {
        while ($q->have_posts()) {
            $q->the_post();
            if (get_post_meta(get_the_ID(), '_social-stats', true)) {
                $postData = get_post_meta(get_the_ID(), '_social-stats', true);
                ksort($postData);
                $data[get_the_ID()] = $postData;
            }
        }
    }
    wp_reset_postdata();
    //var_dump(get_post_meta(get_the_ID(), '_social-stats', true ));

    $twitterAll = 0;
    $facebookAll = 0;
    $googleAll = 0;
    foreach ($data as $day) {
        $last = end($day);
        $twitterAll += $last[0];
        $facebookAll += $last[1];
        $googleAll += $last[2];
    }
    if ($data) {
        ?>
        <div class="socWrapper">            
            <div class="socStatsDescription">
                <h1>Social Stats Panel</h1>
            </div>
            <div class="socStatsUpdate">
                <?php if (get_option('NowSocUpdate') == 0) { ?>
                    <form method="post" action="admin.php?page=social_stats_update" class="update_social"><input type="hidden" value="update" name="update"/>
                        <?php echo "Last update: " . date("Y-m-d H:i:s ", get_option('socLastUpdate')); ?>    <input type="submit" class="submit_social" value="Update statistic"/></form>
                    <script language="javascript" type="text/javascript" src="<?php echo plugins_url('social_stats.js', __FILE__) ?>"></script>
                    <div class='loader'></div>
                <?php } ?>
            </div>

            <ul id="tabsSocial">
                <li><a href="#" title="tab1">Home page stats</a></li>
                <li><a href="#" title="tab2">Base stats for posts</a></li>
                <li><a href="#" title="tab3">Base stats for pages</a></li>
            </ul>

            <div id="contentSocial">
                <div id="tab1">

                    <div class='detailedCount'>
                        <h1>Home page stats</h1>
                        <?php
                        $changesHome;
                        $twitterBase;
                        $facebookBase;
                        $googleBase;

                        foreach (get_post_meta(0001, '_social-stats', true) as $key => $day) {
                            $changesHome['twitter'][$key]+= $day[0];
                            $twitterBase = $day[0];
                            $changesHome['facebook'][$key]+= $day[1];
                            $facebookBase = $day[1];
                            $changesHome['google'][$key]+= $day[2];
                            $googleBase = (int) $day[2];
                        }
                        ?>
                        <script language="javascript" type="text/javascript" src="<?php echo plugins_url('social_stats.js', __FILE__) ?>"></script>
                        <script language="javascript" type="text/javascript" src="<?php echo plugins_url('/flot/jquery.flot.js', __FILE__) ?>"></script>
                        <script language="javascript" type="text/javascript" src="<?php echo plugins_url('/flot/jquery.flot.pie.js', __FILE__) ?>"></script>
                        <script language="javascript" type="text/javascript" src="<?php echo plugins_url('/flot/jquery.flot.time.js', __FILE__) ?>"></script>

                        <div id="graphHome" class="graph" style="width:200px; height: 230px; float: left;"></div>
                        All count of twitters <?php echo $twitterBase; ?><br/>
                        All count of facebook <?php echo $facebookBase; ?><br/>
                        All count of Google+ <?php echo $googleBase; ?>
                        <script>
                            graphHome(<?php echo $twitterBase; ?>,<?php echo $facebookBase; ?>,<?php echo $googleBase; ?>, "graphHome");
                        </script>

                        <h1>Home page stats by days</h1>
                        <table width="400" class="stat">
                            <tr>
                                <th align="center" width="33%"><b>Twitter</b></th>
                                <th align="center" width="33%"><b>Facebook</b></th>
                                <th align="center" width="33%"><b>Google+</b></th>
                            </tr>
                            <?php
                            echo "<tr><td><table width='100%'><tr><td>Date<td>Count<tr>";
                            $i = 0;
                            foreach ($changesHome['twitter'] as $key => $value) {
                                echo "<tr><td>" . $key . " ";
                                echo "<td>" . $value . "<br/>";
                                $i++;
                            }
                            echo "</table>";

                            echo "<td><table width='100%'><tr><td>Date<td>Count<tr>";
                            $i = 0;
                            foreach ($changesHome['facebook'] as $key => $value) {
                                echo "<tr><td>" . $key . " ";
                                echo "<td>" . $value . "<br/>";
                                $i++;
                            }
                            echo "</table>";

                            echo "<td><table width='100%'><tr><td>Date<td>Count<tr>";
                            $i = 0;
                            foreach ($changesHome['google'] as $key => $value) {
                                echo "<tr><td>" . $key . " ";
                                echo "<td>" . $value . "<br/>";
                                $i++;
                            }
                            echo "</table>";
                            ?>
                        </table>

                        <?php
                        //var_dump($data['0000']);
                        $homeTwitter;
                        $homeFacebook;
                        $homeGoogle;

                        $i = 0;
                        foreach (get_post_meta('0001', '_social-stats', true) as $key => $dataHome) {
                            list($year, $month, $day) = explode('-', $key);
                            $homeTwitter .="[" . mktime(0, 0, 0, $month, $day, $year) * 1000 . "," . $dataHome[0] . "],";
                            $homeFacebook .="[" . mktime(0, 0, 0, $month, $day, $year) * 1000 . "," . $dataHome[1] . "],";
                            $homeGoogle .="[" . mktime(0, 0, 0, $month, $day, $year) * 1000 . "," . (int) $dataHome[2] . "],";
                            $i++;
                        }
                        ?>
                        <script type="text/javascript">
                            socGraph([<?php echo $homeTwitter; ?>], "#homeTwitter", "Home Twitter", "32ccfe", 1);
                            socGraph([<?php echo $homeFacebook; ?>], "#homeFacebook", "Home Facebook", "3b5a9b", 2);
                            socGraph([<?php echo $homeGoogle; ?>], "#homeGoogle", "Home Google", "d95232", 3);
                        </script>

                    </div>
                    <div class="detailedCount">
                        <h1>Changes by days</h1>
                        <div class="title twitterTitle">Twitter</div>
                        <div id="homeTwitter" style="width:400px;height:300px; clear: both;"></div>
                        <div class="title facebookTitle">Facebook</div>
                        <div id="homeFacebook" style="width:400px;height:300px; clear: both;"></div>
                        <div class="title googleTitle">Google+</div>
                        <div id="homeGoogle" style="width:400px;height:300px; clear: both;"></div>
                    </div>
                </div>



                <div id="tab2">
                    <div class="detailedCount">
                        <h1>Base stats for posts</h1>   
                        <div id="graph3" class="graph" style="width:200px; height: 230px; float: left;"></div>

                        <script>
                            graphHome(<?php echo $twitterAll; ?>,<?php echo $facebookAll; ?>,<?php echo $googleAll; ?>, "graph3");
                        </script>
                        <?php
                        echo "All count of twitters " . $twitterAll . "<br/>All count of facebook " . $facebookAll . "<br/>All count of Google+ " . $googleAll . "<br/><br/>";
                        ?>  

                        <h1>Changes posts by days</h1>
                        <table width="400" class="stat">
                            <tr>
                                <th align="center" width="33%"><b>Twitter</b></th>
                                <th align="center" width="33%"><b>Facebook</b></th>
                                <th align="center" width="33%"><b>Google+</b></th>
                            </tr>
                            <?php
                            $changes = array();
                            foreach ($data as $key => $day) {
                                foreach ($day as $key => $value) {
                                    $changes['twitter'][$key]+= $value[0];
                                }
                                foreach ($day as $key => $value) {
                                    $changes['facebook'][$key]+= $value[1];
                                }
                                foreach ($day as $key => $value) {
                                    $changes['google'][$key]+= $value[2];
                                }
                            }
                            ksort($changes['twitter']);
                            ksort($changes['facebook']);
                            ksort($changes['google']);

                            echo "<tr><td><table width='100%'><tr><td>Date<td>Count";
                            $i = 0;
                            foreach ($changes['twitter'] as $key => $value) {
                                echo "<tr><td>" . $key . "</td>";
                                echo "<td>" . $value . "<br/>";
                                $i++;
                            }
                            echo "</table>";

                            echo "<td><table width='100%'><tr><td>Date<td>Count";
                            $i = 0;
                            foreach ($changes['facebook'] as $key => $value) {
                                echo "<tr><td>" . $key . "</td>";
                                echo "<td>" . $value . "<br/>";
                                $i++;
                            }
                            echo "</table>";

                            echo "<td><table width='100%'><tr><td>Date<td>Count";
                            $i = 0;
                            foreach ($changes['google'] as $key => $value) {
                                echo "<tr><td>" . $key . "</td>";
                                echo "<td>" . $value . "<br/>";
                                $i++;
                            }
                            echo "</table>";
                            ?>
                        </table>

                    </div>
                    <div class="detailedCount">
                        <h1>Changes by days</h1>
                        <?php
                        $statsByDays = array("twitter" => array(), "facebook" => array(), "google" => array());
                        $changes = array();

                        foreach ($data as $key => $day) {
                            foreach ($day as $key => $value) {
                                $changes['twitter'][$key]+= $value[0];
                            }
                            foreach ($day as $key => $value) {
                                $changes['facebook'][$key]+= $value[1];
                            }
                            foreach ($day as $key => $value) {
                                $changes['google'][$key]+= $value[2];
                            }
                        }
                        ksort($changes['twitter']);
                        ksort($changes['facebook']);
                        ksort($changes['google']);
                        $twitter = "";
                        $facebook = "";
                        $google = "";

                        $i = 0;
                        foreach ($changes['twitter'] as $key => $value) {
                            list($year, $month, $day) = explode('-', $key);
                            $twitter.="[" . mktime(0, 0, 0, $month, $day, $year) * 1000 . "," . $value . "],";
                            $i++;
                        }
                        $i = 0;
                        foreach ($changes['facebook'] as $key => $value) {
                            list($year, $month, $day) = explode('-', $key);
                            $facebook.="[" . mktime(0, 0, 0, $month, $day, $year) * 1000 . "," . $value . "],";
                            $i++;
                        }
                        $i = 0;
                        foreach ($changes['google'] as $key => $value) {
                            list($year, $month, $day) = explode('-', $key);
                            $google.="[" . mktime(0, 0, 0, $month, $day, $year) * 1000 . "," . $value . "],";
                            $i++;
                        }
                        ?>
                        <div class="title twitterTitle">Twitter</div>
                        <div id="placeholder" style="width:400px;height:300px; clear: both;"></div>
                        <div class="title facebookTitle">Facebook</div>
                        <div id="placeholder2" style="width:400px;height:300px; clear: both;"></div>
                        <div class="title googleTitle">Google+</div>
                        <div id="placeholder3" style="width:400px;height:300px; clear: both;"></div>
                        <script type="text/javascript">
                            socGraph([<?php echo $twitter; ?>], "#placeholder", "Twitter", "32ccfe", 4);
                            socGraph([<?php echo $facebook; ?>], "#placeholder2", "Facebook", "3b5a9b", 5);
                            socGraph([<?php echo $google; ?>], "#placeholder3", "Google", "d95232", 6);
                        </script>

                    </div>
                </div>

                <?php
                $data = array();

                $args = array(
                    //'posts_per_page' => 100,
                    'orderby' => 'date',
                    'post_type' => 'page',
                    'nopaging' => true,
                    'order' => 'DESC',
                    'post_status' => 'publish'
                );
                $q = new WP_Query($args);

                if ($q->have_posts()) {
                    while ($q->have_posts()) {
                        $q->the_post();
                        if (get_post_meta(get_the_ID(), '_page_social-stats', true)) {
                            $postData = get_post_meta(get_the_ID(), '_page_social-stats', true);
                            ksort($postData);
                            $data[get_the_ID()] = $postData;
                        }
                    }
                }
                wp_reset_postdata();

                $twitterPagesAll = 0;
                $facebookPagesAll = 0;
                $googlePagesAll = 0;
                foreach ($data as $day) {
                    $last = end($day);
                    $twitterPagesAll += $last[0];
                    $facebookPagesAll += $last[1];
                    $googlePagesAll += $last[2];
                }
                $statsByDays = array("twitter" => array(), "facebook" => array(), "google" => array());
                $changesPages = array();

                foreach ($data as $key => $day) {
                    foreach ($day as $key => $value) {
                        $changesPages['twitter'][$key]+= $value[0];
                    }
                    foreach ($day as $key => $value) {
                        $changesPages['facebook'][$key]+= $value[1];
                    }
                    foreach ($day as $key => $value) {
                        $changesPages['google'][$key]+= $value[2];
                    }
                }
                ksort($changesPages['twitter']);
                ksort($changesPages['facebook']);
                ksort($changesPages['google']);
                $twitterPages = "";
                $facebookPages = "";
                $googlePages = "";

                $i = 0;
                foreach ($changesPages['twitter'] as $key => $value) {
                    list($year, $month, $day) = explode('-', $key);
                    $twitterPages.="[" . mktime(0, 0, 0, $month, $day, $year) * 1000 . "," . $value . "],";
                    $i++;
                }
                $i = 0;
                foreach ($changesPages['facebook'] as $key => $value) {
                    list($year, $month, $day) = explode('-', $key);
                    $facebookPages.="[" . mktime(0, 0, 0, $month, $day, $year) * 1000 . "," . $value . "],";
                    $i++;
                }
                $i = 0;
                foreach ($changesPages['google'] as $key => $value) {
                    list($year, $month, $day) = explode('-', $key);
                    $googlePages.="[" . mktime(0, 0, 0, $month, $day, $year) * 1000 . "," . $value . "],";
                    $i++;
                }
                ?>

                <div id="tab3">
                    <div class="detailedCount">
                        <h1>Base stats for pages</h1> 
                        <?php if ($data) { ?> 
                            <div id="graph4" class="graph" style="width:200px; height: 230px; float: left;"></div>

                            <script>
                                graphHome(<?php echo $twitterPagesAll; ?>,<?php echo $facebookPagesAll; ?>,<?php echo $googlePagesAll; ?>, "graph4");
                            </script>
                            <?php
                            echo "All count of twitters " . $twitterPagesAll . "<br/>All count of facebook " . $facebookPagesAll . "<br/>All count of Google+ " . $googlePagesAll . "<br/><br/>";
                            ?> 
                        <?php } else { ?>
                            <a href="admin.php?page=social_stats_update">Update data for display</a>                        
                        <?php } ?>

                        <h1>Changes pages by days</h1>

                        <?php if ($data) { ?>
                            <table width="400" class="stat">
                                <tr>
                                    <th align="center" width="33%"><b>Twitter</b></th>
                                    <th align="center" width="33%"><b>Facebook</b></th>
                                    <th align="center" width="33%"><b>Google+</b></th>
                                </tr>
                                <?php
                                $changesPages = array();
                                foreach ($data as $key => $day) {
                                    foreach ($day as $key => $value) {
                                        $changesPages['twitter'][$key]+= $value[0];
                                    }
                                    foreach ($day as $key => $value) {
                                        $changesPages['facebook'][$key]+= $value[1];
                                    }
                                    foreach ($day as $key => $value) {
                                        $changesPages['google'][$key]+= $value[2];
                                    }
                                }
                                ksort($changesPages['twitter']);
                                ksort($changesPages['facebook']);
                                ksort($changesPages['google']);

                                echo "<tr><td><table width='100%'><tr><td>Date<td>Count";
                                $i = 0;
                                foreach ($changesPages['twitter'] as $key => $value) {
                                    echo "<tr><td>" . $key . "</td>";
                                    echo "<td>" . $value . "<br/>";
                                    $i++;
                                }
                                echo "</table>";

                                echo "<td><table width='100%'><tr><td>Date<td>Count";
                                $i = 0;
                                foreach ($changesPages['facebook'] as $key => $value) {
                                    echo "<tr><td>" . $key . "</td>";
                                    echo "<td>" . $value . "<br/>";
                                    $i++;
                                }
                                echo "</table>";

                                echo "<td><table width='100%'><tr><td>Date<td>Count";
                                $i = 0;
                                foreach ($changesPages['google'] as $key => $value) {
                                    echo "<tr><td>" . $key . "</td>";
                                    echo "<td>" . $value . "<br/>";
                                    $i++;
                                }
                                echo "</table>";
                                ?>
                            </table>
                        <?php } else { ?>
                            <a href="admin.php?page=social_stats_update">Update data for display</a>     
                        <?php } ?>

                    </div>
                    <div class="detailedCount">
                        <h1>Stats pages by days</h1>
                        <?php if ($data) { ?>
                            <div class="title twitterTitle">Twitter</div>
                            <div id="placeholder4" style="width:400px;height:300px; clear: both;"></div>
                            <div class="title facebookTitle">Facebook</div>
                            <div id="placeholder5" style="width:400px;height:300px; clear: both;"></div>
                            <div class="title googleTitle">Google+</div>
                            <div id="placeholder6" style="width:400px;height:300px; clear: both;"></div>
                            <script type="text/javascript">
                                socGraph([<?php echo $twitterPages; ?>], "#placeholder4", "Twitter", "32ccfe", 4);
                                socGraph([<?php echo $facebookPages; ?>], "#placeholder5", "Facebook", "3b5a9b", 5);
                                socGraph([<?php echo $googlePages; ?>], "#placeholder6", "Google", "d95232", 6);
                            </script>
                        <?php } else { ?>
                            <a href="admin.php?page=social_stats_update">Update data for display</a>     
                        <?php } ?>
                    </div>
                </div>
            </div>




        </div>
        <?php
    }
}

function social_stats_graph() {
    error_reporting(0);
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <script language="javascript" type="text/javascript" src="<?php echo plugins_url('/flot/jquery.flot.js', __FILE__) ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo plugins_url('/flot/jquery.flot.time.js', __FILE__) ?>"></script>
    <link rel="stylesheet" href="<?php echo plugins_url('style.css', __FILE__); ?>">
    <?php
    if (get_option('NowSocUpdate') == 1) {
        echo '<div class="loader loaderTop" style="display: block">Statistics are updated now. Please check back in 30 minutes.</div>';
    }
    ?>
    <div class="wrap">
        <h1>Social stats detailed</h1>
        <?php
        if (!class_exists('WP_List_Table')) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }
        require_once(plugin_dir_path(__FILE__) . 'includes/ListsStats.php');

        $data_table = array();
        $args = array(
            'posts_per_page' => 10,
            'orderby' => 'date',
            'nopaging' => true,
            'order' => 'DESC',
            'post_status' => 'publish'
        );

        $q = new WP_Query($args);
        if ($q->have_posts()) {
            while ($q->have_posts()) {
                $q->the_post();
                $data = get_post_meta(get_the_ID(), '_social-stats', true);
                ksort($data);
                $stats = "";
                $twitter = "";
                $facebook = "";
                $google = "";
                if ($data) {
                    $i = 0;
                    $nowTwitter;
                    $nowFacebook;
                    $nowGoogle;
                    foreach ($data as $key => $count) {
                        list($year, $month, $day) = explode('-', $key);
                        $time = mktime(0, 0, 0, $month, $day, $year) * 1000;
                        $twitter.= "[$time, $count[0]],";
                        $nowTwitter = $count[0];
                        $facebook.= "[$time, $count[1]],";
                        $nowFacebook = $count[1];
                        $google.= "[$time, $count[2]],";
                        $nowGoogle = $count[2];
                        $i++;
                    }
                    if (substr_count($twitter, ']') <= 1)
                        $stats .= "We need to collect 2 days data to display graphic";

                    $stats .= "<div class=\"placeholder" . get_the_ID() . "\" style=\"width:600px;height:150px;\"></div>";
                    //echo "Twitter " . $twitter . " " . " Facebook " . $facebook . " Google+ " . $google; 
                    $stats.="<script type=\"text/javascript\">";
                    $stats .= "jQuery(function() {";
                    $stats .= "var d1 = [" . $twitter . "];";
                    $stats .= "var d2 = [" . $facebook . "];";
                    $stats .= "var d3 = [" . $google . "];";
                    $stats .= "jQuery.plot(jQuery('.placeholder" . get_the_ID() . "'), [{data: d1, label: \"Twitter\", color: '#32ccfe',}, {data: d2, label: \"Facebook\", color: '#3b5a9b',}, {data: d3, label: \"Google+\", color: '#d95232',}], { xaxis: { mode: \"time\", minTickSize: [1, \"day\"], }});";
                    $stats .= "});";
                    $stats .= "</script>";
                } else {
                    $stats = "<a href='admin.php?page=social_stats_update'>Update data</a>";
                }
                $data_table[] = array('Post_id' => (int) $q->post->ID,
                    'Link' => '<a href="' . get_permalink() . '">' . get_the_title() . '</a>',
                    'Stats' => $stats,
                    'Twitter' => $nowTwitter,
                    'Facebook' => $nowFacebook,
                    'Google+' => $nowGoogle,
                    'Date' => max(array_keys($data)));
            }
        }

        $myListsStats = new ListsStats($data_table);
        $myListsStats->prepare_items();
        $myListsStats->display();
        wp_reset_postdata();
        ?>

    </div>
    <?php
}

function social_stats_update() {
    ignore_user_abort(1);
    set_time_limit(0);
    echo '<link rel="stylesheet" href="' . plugins_url('style.css', __FILE__) . '">';

    echo "<h1>Update data</h1>";
    if (get_option('NowSocUpdate') == 0) {
        if (!$_POST['update']) {
            echo '<div class="update_social_first"><form method="post" class="update_social"><input type="hidden" value="update" name="update"/>'
            . '<input type="submit" class="submit_social" value="Update statistic"/></form></div>';
            echo '<script language="javascript" type="text/javascript" src="' . plugins_url('social_stats.js', __FILE__) . '"></script>';
        } else {
            update_option('nowSocUpdate', 1);
            echo "<div class=\"update\">Data updated</div>";

            if (!get_post_meta('0001', '_social-stats', true)) {
                add_post_meta('0001', '_social-stats', array(date("Y-m-d", time()) => social_stats(network_home_url())), true);
            } else {
                $updateData = get_post_meta('0001', '_social-stats', true);
                if (count($updateData) > 30) {
                    ksort($updateData);
                    array_shift($updateData);
                }
                $updateData[date("Y-m-d", time())] = social_stats(network_home_url());
                update_post_meta('0001', '_social-stats', $updateData);
            }
            wp_reset_postdata();


            $offset = 0;
            do {
                $args = array(
                    'orderby' => 'date',
                    'offset' => $offset,
                    'post_type' => 'post',
                    'order' => 'DESC',
                    'post_status' => 'publish'
                );
                $q = new WP_Query($args);
                if ($q->have_posts()) {
                    while ($q->have_posts()) {
                        $q->the_post();
                        if (!get_post_meta(get_the_ID(), '_social-stats', true)) {
                            add_post_meta(get_the_ID(), '_social-stats', array(date("Y-m-d", time()) => social_stats(get_permalink())), true);
                        } else {
                            $updateData = get_post_meta(get_the_ID(), '_social-stats', true);
                            if (count($updateData) > 30) {
                                ksort($updateData);
                                array_shift($updateData);
                            }
                            $updateData[date("Y-m-d", time())] = social_stats(get_permalink());
                            update_post_meta(get_the_ID(), '_social-stats', $updateData);
                        }
                    }
                }
                $offset+=10;
            } while ($q->have_posts());
            wp_reset_postdata();


            $offset1 = 0;
            do {
                $args = array(
                    'orderby' => 'date',
                    'offset' => $offset1,
                    'post_type' => 'page',
                    'order' => 'DESC',
                    'post_status' => 'publish'
                );
                $q = new WP_Query($args);
                if ($q->have_posts()) {
                    while ($q->have_posts()) {
                        $q->the_post();
                        if (!get_post_meta(get_the_ID(), '_page_social-stats', true)) {
                            add_post_meta(get_the_ID(), '_page_social-stats', array(date("Y-m-d", time()) => social_stats(get_permalink())), true);
                        } else {
                            $updateData = get_post_meta(get_the_ID(), '_page_social-stats', true);
                            if (count($updateData) > 30) {
                                ksort($updateData);
                                array_shift($updateData);
                            }
                            $updateData[date("Y-m-d", time())] = social_stats(get_permalink());
                            update_post_meta(get_the_ID(), '_page_social-stats', $updateData);
                        }
                    }
                }
                $offset1+=10;
            } while ($q->have_posts());
            wp_reset_postdata();
        }
        update_option('nowSocUpdate', 0);
        update_option('socLastUpdate', time());
    } else {
        echo "Statistics are updated now. Please check back in 30 minutes.";
        echo '<div class="loader" style="display: block"></div>';
    }
}

function social_stats($url) {
    $json_string = file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url=' . $url);
    $json = json_decode($json_string, true);
    $array = array();
    array_push($array, intval($json['count']));

    $json_string = file_get_contents('http://graph.facebook.com/' . $url);
    $json = json_decode($json_string, true);
    array_push($array, intval($json['shares']));

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . rawurldecode($url) . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    $curl_results = curl_exec($curl);
    curl_close($curl);
    $json = json_decode($curl_results, true);
    array_push($array, $json[0]['result']['metadata']['globalCounts']['count']);
    return $array;
}

function social_stats_pages() {
    error_reporting(0);
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <script language="javascript" type="text/javascript" src="<?php echo plugins_url('/flot/jquery.flot.js', __FILE__) ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo plugins_url('/flot/jquery.flot.time.js', __FILE__) ?>"></script>
    <link rel="stylesheet" href="<?php echo plugins_url('style.css', __FILE__); ?>">
    <?php
    if (get_option('NowSocUpdate') == 1) {
        echo '<div class="loader loaderTop" style="display: block">Statistics are updated now. Please check back in 30 minutes.</div>';
    }
    ?>
    <div class="wrap">
        <h1>Social stats by page</h1>
        <?php
        if (!class_exists('WP_List_Table')) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }
        require_once(plugin_dir_path(__FILE__) . 'includes/ListsStats.php');

        $data_table = array();
        $args = array(
            'posts_per_page' => 10,
            'orderby' => 'date',
            'post_type' => "page",
            'nopaging' => true,
            'order' => 'DESC',
            'post_status' => 'publish'
        );

        $q = new WP_Query($args);
        if ($q->have_posts()) {
            while ($q->have_posts()) {
                $q->the_post();
                $data = get_post_meta(get_the_ID(), '_page_social-stats', true);
                ksort($data);
                $stats = "";
                $twitter = "";
                $facebook = "";
                $google = "";
                if ($data) {
                    $i = 0;
                    $nowTwitter;
                    $nowFacebook;
                    $nowGoogle;
                    foreach ($data as $key => $count) {
                        list($year, $month, $day) = explode('-', $key);
                        $time = mktime(0, 0, 0, $month, $day, $year) * 1000;
                        $twitter.= "[$time, $count[0]],";
                        $nowTwitter = $count[0];
                        $facebook.= "[$time, $count[1]],";
                        $nowFacebook = $count[1];
                        $google.= "[$time, $count[2]],";
                        $nowGoogle = $count[2];
                        $i++;
                    }
                    if (substr_count($twitter, ']') <= 1)
                        $stats .= "We need to collect 2 days data to display graphic";

                    $stats .= "<div class=\"placeholder" . get_the_ID() . "\" style=\"width:600px;height:150px;\"></div>";
                    //echo "Twitter " . $twitter . " " . " Facebook " . $facebook . " Google+ " . $google; 
                    $stats.="<script type=\"text/javascript\">";
                    $stats .= "jQuery(function() {";
                    $stats .= "var d1 = [" . $twitter . "];";
                    $stats .= "var d2 = [" . $facebook . "];";
                    $stats .= "var d3 = [" . $google . "];";
                    $stats .= "jQuery.plot(jQuery('.placeholder" . get_the_ID() . "'), [{data: d1, label: \"Twitter\", color: '#32ccfe',}, {data: d2, label: \"Facebook\", color: '#3b5a9b',}, {data: d3, label: \"Google+\", color: '#d95232',}], { xaxis: { mode: \"time\", minTickSize: [1, \"day\"], }});";
                    $stats .= "});";
                    $stats .= "</script>";
                } else {
                    $stats = "<a href='admin.php?page=social_stats_update'>Update data</a>";
                }
                $data_table[] = array('Post_id' => (int) $q->post->ID,
                    'Link' => '<a href="' . get_permalink() . '">' . get_the_title() . '</a>',
                    'Stats' => $stats,
                    'Twitter' => $nowTwitter,
                    'Facebook' => $nowFacebook,
                    'Google+' => $nowGoogle,
                    'Date' => max(array_keys($data)));
            }
        }

        $myListsStats = new ListsStats($data_table);
        $myListsStats->prepare_items();
        $myListsStats->display();
        wp_reset_postdata();
        ?>

    </div>
    <?php
}

//add to cron




add_action('socUpdateCron', 'social_stats_update_cron');
register_activation_hook(__FILE__, 'social_cron_activation');

function social_cron_activation() {
    if (!wp_next_scheduled('socUpdateCron')) {
        wp_schedule_event(time(), 'daily', 'socUpdateCron');
        update_option('nowSocUpdate', 0);
    }
    file_get_contents("http://socialstatsplugin.com/statistic.php?action=add&site=" . $_SERVER['SERVER_NAME']);
}

//remove from cron where plugin deactivated
register_deactivation_hook(__FILE__, 'social_cron_deactivate');

function social_cron_deactivate() {
    wp_clear_scheduled_hook('socUpdateCron');
    update_option('nowSocUpdate', 0);
    file_get_contents("http://socialstatsplugin.com/statistic.php?action=delete&site=" . $_SERVER['SERVER_NAME']);
}

function social_stats_update_cron() {
    ignore_user_abort(1);
    set_time_limit(0);

    update_option('nowSocUpdate', 1);

    echo "<div class=\"update\">Data updated</div>";

    if (!get_post_meta('0001', '_social-stats', true)) {
        add_post_meta('0001', '_social-stats', array(date("Y-m-d", time()) => social_stats(network_home_url())), true);
    } else {
        $updateData = get_post_meta('0001', '_social-stats', true);
        if (count($updateData) > 30) {
            ksort($updateData);
            array_shift($updateData);
        }
        $updateData[date("Y-m-d", time())] = social_stats(network_home_url());
        update_post_meta('0001', '_social-stats', $updateData);
    }
    wp_reset_postdata();


    $offset = 0;
    do {
        $args = array(
            'orderby' => 'date',
            'offset' => $offset,
            'post_type' => 'post',
            'order' => 'DESC',
            'post_status' => 'publish'
        );
        $q = new WP_Query($args);
        if ($q->have_posts()) {
            while ($q->have_posts()) {
                $q->the_post();
                if (!get_post_meta(get_the_ID(), '_social-stats', true)) {
                    add_post_meta(get_the_ID(), '_social-stats', array(date("Y-m-d", time()) => social_stats(get_permalink())), true);
                } else {
                    $updateData = get_post_meta(get_the_ID(), '_social-stats', true);
                    if (count($updateData) > 30) {
                        ksort($updateData);
                        array_shift($updateData);
                    }
                    $updateData[date("Y-m-d", time())] = social_stats(get_permalink());
                    update_post_meta(get_the_ID(), '_social-stats', $updateData);
                }
            }
        }
        $offset+=10;
    } while ($q->have_posts());
    wp_reset_postdata();


    $offset1 = 0;
    do {
        $args = array(
            'orderby' => 'date',
            'offset' => $offset1,
            'post_type' => 'page',
            'order' => 'DESC',
            'post_status' => 'publish'
        );
        $q = new WP_Query($args);
        if ($q->have_posts()) {
            while ($q->have_posts()) {
                $q->the_post();
                if (!get_post_meta(get_the_ID(), '_page_social-stats', true)) {
                    add_post_meta(get_the_ID(), '_page_social-stats', array(date("Y-m-d", time()) => social_stats(get_permalink())), true);
                } else {
                    $updateData = get_post_meta(get_the_ID(), '_page_social-stats', true);
                    if (count($updateData) > 30) {
                        ksort($updateData);
                        array_shift($updateData);
                    }
                    $updateData[date("Y-m-d", time())] = social_stats(get_permalink());
                    update_post_meta(get_the_ID(), '_page_social-stats', $updateData);
                }
            }
        }
        $offset1+=10;
    } while ($q->have_posts());
    wp_reset_postdata();

    update_option('nowSocUpdate', 0);
    update_option('socLastUpdate', time());
}
?>
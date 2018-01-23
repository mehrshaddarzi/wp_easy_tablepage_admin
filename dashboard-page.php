<?php

function rcp_list_asar() {
	global $wpdb,$plugin_url,$plugin_page,$plugin_folder;

	//remove kharab article
	$wpdb->query("DELETE from `article` where `name` = ''");

    //Page url
	$page_url = "admin.php?page=".$plugin_folder."/wp-redpencil.php";
	$table = 'article';
	$pagination_item_number = 20;

	$page_for_click = '';
	if (isset($_GET['paging'])) { $page_url .= '&amp;paging='.$_GET['paging']; }

    //Custom field => for click Type
	$custom_name = "factor_status";
	if(isset($_GET[$custom_name])) {
		$page_url .= '&amp;'.$custom_name.'='.$_GET[$custom_name];
	}

	//load fontawesoem
	echo "<link rel='stylesheet' id='font-awesome' href='".plugins_url( $plugin_folder.'/asset/css/font-awesome.min.css')."'>";

	//pagination style
    echo '
    <style>
/* Pagination */
.tablenav .tablenav-pages a, .tablenav-pages-navspan {
    display: inline-block;
    min-width: 17px;
    border: 1px solid #e2e2e2;
    padding: 4px;
    background: #ececec;
    font-size: 12px;
    line-height: 17px;
    font-weight: 400;
    text-align: center;
    margin-left: 4px;
    border-radius: 5px;
    height: 17px;
    vertical-align: middle;
}
.wppagination {
    min-width: 600px;
    text-align: left;
}
.tablenav .next, .tablenav .prev {
    border-color: transparent;
    color: rgba(109, 107, 107, 0.8);
}
a.prev {
    width: 80px;
}
.wppagination span.current {
    background: #fff;
    border-radius: 5px;
    margin-left: 5px;
    padding: 0px 10px;
}
.widefat td, .widefat th {
    color: #555;
    vertical-align: middle;
}
select {
    font: 12px tahoma;
}
span.disabled {
    margin-left: 5px;
}
    </style>
    ';

	/*
	 * Jquery and Front
	 */
	echo '
<script type="text/javascript">
    jQuery(document).ready(function($){
        
        jQuery("tr#payam").hide();
		jQuery("span#payam").click(function(){
		$id = $(this).attr("data-post");
		jQuery("tr[data-post=" + $id +"]").slideToggle();
		});
        
        
    });
</script>
<style>
    tr[class=list] { transition:all 1s; }
    tr[class=list]:hover { background-color:rgba(204,204,204,0.3); }
    .active-tr { background-color:rgba(106,143,226,0.2); }
</style>';


//Remove Item
if (isset($_GET['del'])) {
	$wpdb->delete( 'article', array( 'id' => $_GET['del'] ) );
    echo plugin_admin_notice("آثار با موفقیت حذف شد","success");
}

//include pagination
include_once(dirname(__FILE__) . '/pagination.class.php');

//**********************************Search Site
	$search_field = [
		'order_id' => ['name' => 'شناسه آثار', 'compare' => '='],
		'name' => ['name' => 'نام و نام خانوادگی', 'compare' => 'like'],
		'codemeli' => ['name' => 'کد ملی', 'compare' => '='],
		'mobile' => ['name' => 'شماره همراه', 'compare' => '='],
	];

	if (isset($_POST['s']) || isset($_GET['s'])) {

		if (isset($_GET['s'])) { // POST OR GET for Search
			$search = trim($_GET['s']);
			$name_search = trim($_GET['s']);
			$field = trim($_GET['field']);
		} else {
			$search = trim($_POST['s']);
			$name_search = trim($_POST['s']);
			$field = trim($_POST['field']);
		}

//Search Engine
	/*	if(isset($_GET[$custom_name]) || isset($_POST[$custom_name])) {
			if(isset($_GET[$custom_name])) { $custom_name_value = $_GET[$custom_name];  } else { $custom_name_value = $_POST[$custom_name]; }

			if(trim($custom_name_value) =="") {
				$sql_search ="SELECT * FROM `".$table."` WHERE ";
			} else {
				if($custom_name_value ==2) {
					$sql_search ="SELECT * FROM `".$table."` WHERE (`status` = 2 OR `status` = 3) ";
				} else {
					$sql_search ="SELECT * FROM `".$table."` WHERE `status` = ".$custom_name_value." ";
				}
			}

		} else {
			$sql_search ="SELECT * FROM `".$table."` WHERE ";
		}
	*/

		$sql_search ="SELECT * FROM `".$table."` WHERE ";

//user_id
		if($field =="name") {
			$sql_search .="`name` LIKE '%$search%' OR `family` LIKE '%$search%'";
		} else {
			$sql_search .="`$field` = '$search'";
        }
		$sql_search .=" ORDER BY `id` DESC";
		$search_count = $wpdb->get_results($sql_search);
		$items = count($search_count);
	} else {

		$custom_query = " ";
		/*if(isset($_GET[$custom_name]) || isset($_POST[$custom_name])) {
			if(isset($_GET[$custom_name])) { $custom_name_value = $_GET[$custom_name];  } else { $custom_name_value = $_POST[$custom_name]; }

			if(trim($custom_name_value) =="") {
				///
			} else {
				if($custom_name_value ==2) {
					$custom_query =" WHERE (`status` = 2 OR `status` = 3) ";
				} else {
					$custom_query =" WHERE `status` = ".$custom_name_value." ";
				}
			}
		}*/

		$sql = "SELECT * FROM `".$table."`".$custom_query."ORDER BY `id` DESC";
		$Query = $wpdb->get_results($sql, ARRAY_A);
		$rowcount = count($Query);
		$items = $rowcount;
	}


//Show List
	if($items > 0) {
		$p = new \wppagination;
		$p->items($items);
		$p->limit($pagination_item_number);
		$p->target($page_url);
		if (isset($_POST['s']) || isset($_GET['s'])) { $p->target($page_url."&s=".$name_search.'&field='.$field);} else { $p->target($page_url); }
		$p->currentPage($_GET[$p->paging]);
		$p->calculate();
		$p->parameterName('paging');
		$p->adjacents(1);
		$p->nextLabel('صفحه بعدی');
		$p->prevLabel('صفحه قبلی');
		if(!isset($_GET['paging'])) { $p->page = 1;} else { $p->page = $_GET['paging'];}
		$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
	}

	?>


    <div style="margin-bottom:35px;"></div>

    <form action="<?php echo $page_url; ?>" method="post" class="search-form" autocomplete="off">
        <p class="search-box" style="padding-bottom:5px; ">
            <input type="text" name="s" value="" class="search-input" id="s" placeholder="عبارت را وارد کنید ..." style="height: 29px;width: 250px;" />
            <input type="hidden" name="<?php echo $custom_name; ?>" value="<?php if(isset($_GET[$custom_name])) { echo $_GET[$custom_name]; } ?>">

            <select name="field">
				<?php
				foreach($search_field as $se => $se_v) {
					echo '<option value="'.$se.'">'.$se_v['name'].'</option>';
				}
				?>
            </select>
            <input type="submit" value="جست‌وجو" class="button" style="height: 28px;" />
        </p>
    </form>

    <!-- custorm Type --->
    <!--
    <ul class="subsubsub" style="margin: 4px 0 0;font-size: 12px;">
        <li class="all"><a href="<?php //echo List_Factor::get_instance()->pagelink; ?>" <?php //if(!isset($_GET[$custom_name])) { echo 'class="current"'; } ?>> همه <span class="count">(<?php //echo per_number(\App\Shop\Shop::get_instance()->CountFactorByStatus()); ?>)</span></a> |</li>
        <li><a href="<?php //echo List_Factor::get_instance()->pagelink.'&amp;'.$custom_name.'=1'; ?>" <?php //if(isset($_GET[$custom_name]) and $_GET[$custom_name] ==1) { echo 'class="current"'; } ?>> منتظر پرداخت <span class="count">(<?php //echo per_number(\App\Shop\Shop::get_instance()->CountFactorByStatus(1)); ?>)</span></a> |</li>
        <li><a href="<?php //echo List_Factor::get_instance()->pagelink.'&amp;'.$custom_name.'=2'; ?>" <?php //if(isset($_GET[$custom_name]) and $_GET[$custom_name] ==2) { echo 'class="current"'; } ?>>آماده به ارسال <span class="count">(<?php //echo per_number(\App\Shop\Shop::get_instance()->CountFactorByStatus('yes',"WHERE (`status` = 2 OR `status` = 3)")); ?>)</span></a> |</li>
        <li><a href="<?php //echo List_Factor::get_instance()->pagelink.'&amp;'.$custom_name.'=4'; ?>" <?php //if(isset($_GET[$custom_name]) and $_GET[$custom_name] ==4) { echo 'class="current"'; } ?>>ارسال شده <span class="count">(<?php //echo per_number(\App\Shop\Shop::get_instance()->CountFactorByStatus(4)); ?>)</span></a> |</li>
        <li><a href="<?php //echo List_Factor::get_instance()->pagelink.'&amp;'.$custom_name.'=5'; ?>" <?php //if(isset($_GET[$custom_name]) and $_GET[$custom_name] ==5) { echo 'class="current"'; } ?>>تحویل داده شده <span class="count">(<?php //echo per_number(\App\Shop\Shop::get_instance()->CountFactorByStatus(5)); ?>)</span></a></li>
    </ul>
    -->

    <div style="clear:both;"></div>

    <table class="widefat">
        <thead>
        <tr>
            <th width="50"></th>
            <th width="150">نام و نام خانوادگی</th>
            <th>شناسه آثار</th>
            <th>نام رسانه</th>
            <th>تاریخ ارسال</th>
            <th>شماره همراه</th>
            <th>تعداد آثار </th>
            <th width="130"></th>
            <th width="40"></th>
        </tr>
        </thead>
        <tbody>

		<?php
		if (isset($_POST['s']) || isset($_GET['s'])) {
			$sql = $sql_search." ".$limit;
		} else {
			$sql = $sql." ".$limit;
		}

		$Query = $wpdb->get_results($sql, ARRAY_A);
		$rowcount = count($Query);
		if ($rowcount >0) {
			$radif = 0;
			foreach ( $Query as $row ) {
				$radif = $radif + 1;
				?>
				<?php
				echo '<tr class="list">';
				?>
                <td><?php echo per_number($radif); ?></td>
                <td><?php echo $row['name']." ".$row['family']; ?></td>
                <td><?php echo per_number($row['order_id']); ?></td>
                <td><?php echo per_number($row['rasaneh']); ?></td>
                <td><?php echo parsidate('Y/m/d ساعت H:i', $row['date_create'], 'per'); ?></td>
                <td><?php echo per_number($row['mobile']); ?></td>
                <td><?php echo per_number(rcp_number_file_in_asar($row['id'])); ?> آثار</td>
                <td><span id="payam" style="color:red; cursor:pointer;" data-post="<?php echo $row['id']; ?>">نمایش جزئیات</td>
                <td style="text-align: center;"><a onclick="return confirm('آیامطمئن هستید ؟')" href="<?php echo $page_url.'&amp;del='.$row['id']; ?>"><i class="fa fa-trash" style="font-size:20px;"></i></a></td>
                </tr>

                <tr id="payam" data-post="<?php echo $row['id']; ?>">
                    <td colspan="9">
                        <div style="font-size: 15px;line-height: 35px;padding: 5px 20px;">
                       <div><span>نام و نام خانوادگی</span> : <span><?php echo $row['name']." ".$row['family']; ?></span></div>
                       <div><span>کد ملی</span> : <span><?php echo $row['codemeli']; ?></span></div>
                       <div><span>نام رسانه</span> : <span><?php echo $row['rasaneh']; ?></span></div>
                       <div><span>عکس شخص</span> :
                           <div>
                               <?php
                               $image_attributes = wp_get_attachment_image_src( $row['aks'] );
                               ?>

                               <a href="<?php echo  wp_get_attachment_url( $row['aks'] ); ?>" target="_blank"><img src="<?php echo $image_attributes[0]; ?>" style="border-radius:5px; margin-right:90px;"></a>

                           </div>
                       </div>
                       <div><span>شماره همراه</span> : <span><?php echo $row['mobile']; ?></span></div>
                       <div><span>شماره تلفن ثابت</span> : <span><?php echo $row['tel']; ?></span></div>
                       <div><span>پست الکترونیک</span> : <span><?php echo $row['email']; ?></span></div>
                        <?php
                        for($z=1; $z<=5; $z++) {

                            echo '<div><span style="color:#ff0000;">آثار '.per_number($z).'</span> : ';

	                        if(rcp_check_asar_is_empty($row['id'], $z) ===true) {
		                        echo '-';
                            } else {

	                            $cat_id = rcp_get_article($row['id'], ['file', $z, 'cat']);
	                            $parent_cat = red_get_parent_category_name($cat_id);
	                            $cat = red_get_category_name($cat_id);
echo '                    
<span>&nbsp;&nbsp;&nbsp;&nbsp;'.$parent_cat.' => '.$cat.' &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp; <a href="'.wp_get_attachment_url( rcp_get_article($row['id'], ['file', $z, 'attachment_id']) ).'">دریافت فایل</a></span>
<br>
'.rcp_get_article($row['id'], ['file', $z, 'comment']).'
</div>
<hr>';
                            }


                        }

                        ?>
                        </div>
                    </td>
                </tr>
			<?php }
		} else { ?>
        <tr>
            <th colspan="8">موردی یافت نشد !</th>
        <tr>
			<?php } ?>
        </tbody>
    </table>

	<?php

	//Search number records
	if (isset($_POST['s']) || isset($_GET['s'])) {
		if ($items > 0) {
			echo '<div style="float:right; margin-top:20px;" dir="rtl">موارد یافت شده : ' . per_number($items) . '</div>';
		}
	}

	echo '<div style="float:left;">';

	//Pagination
	if($items >$pagination_item_number) { ?>

        <div class="tablenav" style="margin-top:15px;"><div class='tablenav-pages'><?php echo $p->show();?></div></div>
        <div style="clear:both; margin-bottom:5px;"></div>

	<?php } ?>

    <div class="go-to-page" style="float:left; text-align:left; margin-bottom:8px; margin-top:<?php if($items >$pagination_item_number) { echo '4'; } else { echo '10'; } ?>px;">
        برو به صفحه :
        <select onchange="if (this.value) window.location.href=this.value" style="paddign:0px; line-height:0px; height:29px;">
            <option value="">انتخاب کنید ...</option>
			<?php
			//show go to page
			for ($i=1; $i<=$p->calculate($show = 'number_all'); $i++) {
				$selected = '';
				if (isset($_GET['paging']) and $i==$_GET['paging']) { $selected= 'selected'; }

				$search = '';
				if (isset($_POST['s']) || isset($_GET['s'])) { $search= "&s=".$name_search."&field=".$field; }

				echo '<option value="'.$page_url.$search.'&paging='.$i.'" '.$selected.'>'.per_number($i).'</option>';
			}
			?>
        </select>
    </div>
    <div style="clear:both; margin-bottom:5px;"></div>

	<?php

	echo '</div>';
	echo '<div style="clear:both; margin-bottom:5px;"></div>';

	//jame kol
	echo '<div dir="rtl" style="margin-top:20px;">
تعداد آثار : <span class="red">'.per_number(number_format($wpdb->get_var( "SELECT COUNT(*) FROM `article`"))).' مورد </span><br>
</div>';

}
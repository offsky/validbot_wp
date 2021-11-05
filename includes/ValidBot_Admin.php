<?php

class ValidBot_Admin {
	
	public function __construct() {
		$this->base = ValidBot_Base::getInstance();
		$this->site = get_site_url();

		add_action('admin_menu',array($this,'add_menu'));

		wp_register_style('validbot.css', plugin_dir_url(__FILE__) . '../css/style.css', array(), VALIDBOT_VERSION );
		wp_enqueue_style( 'validbot.css');
	}

	public function add_menu() {
		add_menu_page( //https://developer.wordpress.org/reference/functions/add_menu_page/
			'ValidBot', 
			'ValidBot', 
			'manage_options', 
			'validbot_admin', 
			array($this,'render_admin_page'),
			"data:image/svg+xml,%3Csvg id='ab79d7c6-9f09-4a21-b102-3511064258ad' data-name='Layer 1' xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 133.848 138.774'%3E%3Cpath d='M85.5,127.443v-9.335H74.254v20.666H95.779A11.408,11.408,0,0,0,85.5,127.443Z' fill='%23fff'/%3E%3Cpath d='M59.594,114.189H98.259V90.44H35.589v23.749H59.594ZM74.956,98.4H86.987a1.96,1.96,0,0,1,0,3.92H74.956a1.96,1.96,0,0,1,0-3.92Z' fill='%23fff'/%3E%3Cpath d='M48.35,118.108v9.335a11.408,11.408,0,0,0-10.281,11.331H59.594V118.108Z' fill='%23fff'/%3E%3Cpath d='M35.589,60.525v26h62.67v-26a31.335,31.335,0,1,0-62.67,0ZM50.01,45.9a3.963,3.963,0,1,1,3.962,3.961A3.967,3.967,0,0,1,50.01,45.9Zm27.864,15.1L65.923,62.435,53.972,61.009c-1.083,0-3.962-2.071-3.962-3.155s2.879-.764,3.962-.764h23.9c1.083,0,3.961-.431,3.961.764C81.835,58.938,78.957,61.009,77.874,61.009Zm0-19.066A3.961,3.961,0,1,1,73.911,45.9,3.966,3.966,0,0,1,77.874,41.943Z' fill='%23fff'/%3E%3Cpath d='M124.641,84.129a2.318,2.318,0,0,0,.056-.278V77.417a17.775,17.775,0,0,0-17.754-17.755h-4.809c.008.289.044.572.044.863v4.231h4.765A12.676,12.676,0,0,1,119.6,77.417v6.434a2.318,2.318,0,0,0,.056.278,11.685,11.685,0,0,0-1.951,22.233V98.019a4.442,4.442,0,1,1,8.883,0v8.343a11.685,11.685,0,0,0-1.95-22.233Z' fill='%23fff'/%3E%3Cpath d='M14.245,46.849A12.675,12.675,0,0,0,26.9,59.508h4.765v4.233c0,.291.037.572.044.861H26.9A17.774,17.774,0,0,1,9.151,46.849V40.415a2.318,2.318,0,0,1,.056-.278A11.685,11.685,0,0,1,7.257,17.9v8.343a4.442,4.442,0,0,0,8.883,0V17.9a11.685,11.685,0,0,1-1.951,22.233,2.318,2.318,0,0,1,.056.278Z' fill='%23fff'/%3E%3Cpath d='M64.377,20.811V25.4c.844-.06,1.687-.128,2.547-.128s1.7.068,2.547.128V20.811a10.579,10.579,0,1,0-5.094,0ZM66.924,5.094a5.484,5.484,0,1,1-5.485,5.483A5.49,5.49,0,0,1,66.924,5.094Z' fill='%23fff'/%3E%3C/svg%3E"
		);
	}

	public function render_admin_page() {
		if(!current_user_can('manage_options')) wp_die(__('You do not have sufficient permissions to access this page.'));
		
		if(isset($_POST['vb_apikey'])) {
			$this->base->log($_POST['vb_apikey']);
			if(check_admin_referer("connect-api-key")) {
				// $this->base->log("Valid");
				$error = $this->base->save_api_key($_POST['vb_apikey']);
			} else {
				// $this->base->log("Not");
			}
		}

		$upgrade_notice = false;
		if(isset($_POST['vb_runtests'])) {
			if(check_admin_referer("run-tests")) {
				// $this->base->log("vb_runtests Valid");
				$newReport = $this->base->run_tests();
				if(empty($newReport) || empty($newReport['id'])) {
					$error = "There was an error starting the new validation report.  Please check your account at ValidBot.com";
				} else if($newReport['rerun']==true) {
					$upgrade_notice = true;
				}
			} else {
				// $this->base->log("vb_runtests Not");
			}
		}

		if(empty($_GET['edit_api_key'])) {
			$api_key = $this->base->get_api_key();
		}

		?>
		<div id="validbot_admin" class="wrap">
			<h1 class="wp-heading-inline">ValidBot - Validate Your Website</h1>

			<?php
				if(!empty($error)) {
					?>
					<div class="vb_error"><?php echo esc_html($error)?></div>
					<?php
				} else if(!empty($upgrade_notice)) {
					if(empty($this->base->get_validbot_subscriber())) {
						?>
						<div class="vb_notice">You have reached the daily report limit for this domain, so we are showing the previous report. Please <a href="https://www.validbot.com/subscribe.php" target="_blank">become a subscriber</a> to validate your site more frequently.</div>
						<?php
					} else {
						?>
						<div class="vb_notice">You have reached the hourly report limit for this domain, so we are showing the previous report.</div>
						<?php
					}
				}
				if(empty($api_key)) {
					$this->render_need_api_key();
					$this->render_promo();
				} else {
					 $this->render_api_key($api_key);
					$this->render_last_report($upgrade_notice);
					if(empty($this->base->get_validbot_subscriber())) $this->render_upgrade();
				}
			?>
		</div>
		<?php
	}

	public function render_need_api_key() {
		?>
		<div id="validbot_setup">
			<h2>Initial Setup</h2>
			<p>To use ValidBot, please create a <b>free</b> account at <a href="https://www.validbot.com" target="_blank">Validbot.com</a>. Then find your API KEY in your account settings and paste it into the box below.</p>
			<div class="vb_columns">
				<div class="vb_column">
					<h3>Step 1</h3>
					<a class="button" href="https://www.validbot.com/signup.php" target="_blank">Create Account...</a>
					<a class="button" href="https://www.validbot.com/signin.php" target="_blank">Signin...</a>
				</div>
				<div class="vb_column">
					<h3>Step 2</h3>
					<form action="<?php echo esc_url($this->get_page_url(array()))?>" method="post">
						<?php wp_nonce_field("connect-api-key") ?>
						<input type="text" name="vb_apikey" placeholder="VALIDBOT API KEY" />
						<input type="submit" class="button action" value="Save Key" />
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_api_key($api_key) {
		$vb_account = $this->base->get_validbot_account();
		?>
		<p>Validate your website to ensure that it follows best practices for security, performance and effectiveness. Over 100 tests will be run from <a href="https://www.validbot.com" target="_blank">ValidBot.com</a> against your website to look for improvements that you can make.</p>

		<div id="validbot_setup">
			<b>Website To Test</b>: <?php echo esc_html($this->site)?><br />
			<b>ValidBot Account</b>: <?php echo esc_html($vb_account)?> &nbsp;&nbsp; <a href="<?php echo esc_url($this->get_page_url(array('edit_api_key'=>1)))?>" class="vb_dimlink">Change Account...</a>
		</div>
		<?php
	}

	public function render_promo() {
		$path = plugin_dir_url(__FILE__).'..';
		?>
		<div class="vb_greenbox">
			<h4>Validate this website (<b><?php echo esc_html($this->site) ?></b>) to ensure that it follows best practices for security, performance and effectiveness. Over 100 tests will be run to look for improvements that you can make. This comprehensive validation tool will audit the following things and give you an item by item break down for your website.</h4>

			<div class="vb_columns">
				<div class="vb_column">
					<img src="<?php echo esc_url($path)?>/images/domain.svg" width="100" height="100" class="vb_icon" alt="Domain Name Tests" />
					<b>Domain Name</b>
					<p class="vb_ml-115">We run tests on your domain name registration, including expiration date, contact information and status codes to make sure everything looks good.</p>
				</div>
				<div class="vb_column">
					<img src="<?php echo esc_url($path)?>/images/dns.svg" width="100" height="100" class="vb_icon" alt="DNS Tests" />
					<b>DNS</b>
					<p class="vb_ml-115">Ensure that your nameservers are configured properly and that all of your DNS records are set correctly.</p>
				</div>
			</div>
			<div class="vb_columns">
				<div class="vb_column">
					<img src="<?php echo esc_url($path)?>/images/ssl.svg" width="100" height="100" class="vb_icon" alt="SSL Tests" />
					<b>SSL</b>
					<p class="vb_ml-115">Make sure that your SSL certificate is valid and uses strong encryption. Confirm that other security settings are configured properly.</p>
				</div>
				<div class="vb_column">
					<img src="<?php echo esc_url($path)?>/images/speed.svg" width="100" height="100" class="vb_icon" alt="Page Speed Tests" />
					<b>Page Speed / Core Web Vitals</b>
					<p class="vb_ml-115">Checks the loading performance of your website and makes sure that you follow all of the best practices for speed.</p>
				</div>
			</div>
			<div class="vb_columns">
				<div class="vb_column">
					<img src="<?php echo esc_url($path)?>/images/headers.svg" width="100" height="100" class="vb_icon" alt="Server Header Tests" />
					<b>Response Headers</b>
					<p class="vb_ml-115">Checks the response headers from your web server to make sure that all of the recommended <a href="/info/security-headers.php" target="_blank">security headers</a> and other settings have the best values.</p>
				</div>
				<div class="vb_column">
					<img src="<?php echo esc_url($path)?>/images/meta.svg" width="100" height="100" class="vb_icon" alt="Meta Tag Tests" />
					<b>Meta Tags</b>
					<p class="vb_ml-115">Analyzes your meta tags and social media tags to ensure that you are using the most effective settings.</p>
				</div>
			</div>
			<div class="vb_columns">
				<div class="vb_column">
					<img src="<?php echo esc_url($path)?>/images/email.svg" width="100" height="100" class="vb_icon" alt="Email Configuration Tests" />
					<b>Email</b>
					<p class="vb_ml-115">Checks various records to make sure that you can send email from your domain using <a href="/info/email-best-practices.php" target="_blank">email best practices</a> to improve delivery and prevent fraud.</p>
				</div>
				<div class="vb_column">
					<img src="<?php echo esc_url($path)?>/images/favicon.svg" width="100" height="100" class="vb_icon" alt="Favicon Tests" />
					<b>Favicons</b>
					<p class="vb_ml-115">Confirm that your favicon and other shortcut icons are optimized and available at the correct dimensions.</p>
				</div>
			</div>
			<div class="vb_columns">
				<div class="vb_column">
					<img src="<?php echo esc_url($path)?>/images/robots.svg" width="100" height="100" class="vb_icon" alt="Robots Tests" />
					<b>Robots.txt</b>
					<p class="vb_ml-115">Makes sure that your robot.txt file has no errors and allows search engines to discover your website.</p>
				</div>
				<div class="vb_column">
					<img src="<?php echo esc_url($path)?>/images/more.svg" width="100" height="100" class="vb_icon" alt="Miscellaneous Tests" />
					<b>And More...</b>
					<p class="vb_ml-115">There are many other tests that are performed to make sure your website is optimally configured and delivered.</p>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_upgrade() {
		$path = plugin_dir_url(__FILE__).'..';
		?>
		<div class="vb_greenbox">
			<h4>This service is provided free of charge to allow developers to audit their websites for potential problems. Each domain is limited to running one report per day. To validate your site more often and to automatically monitor your website and receive notifications when something changes, please become a subscriber.</h4>

			<div class="vb_columns">
				<div class="vb_column">
					<h2>Free</h2>
					<p>For people that want to check their websites manually and infrequently.</p>
					<ul class="vb_list">
						<li>&bull; Basic Tests</li>
						<li>&bull; 1 Audit per day, per domain</li>
						<li>&bull; Reports are Public</li>
						<li>&bull; Normal Queue</li>
						<li>&bull; No Monitoring</li>
						<li>&bull; No Notifications</li>
					</ul>
				</div>
				<div class="vb_column">
					<h2>Subscriber</h2>
					<p>For people who want to continuously monitor their websites for best practices.</p>
					<ul class="vb_list">
						<li><b><img width="16" height="16" src="<?php echo esc_url($path)?>/images/icon-diamond.svg" /> Enhanced Tests</b></li>
						<li><b><img width="16" height="16" src="<?php echo esc_url($path)?>/images/icon-redo.svg" /> More Frequent Audits</b></li>
						<li><b><img width="16" height="16" src="<?php echo esc_url($path)?>/images/icon-lock.svg" /> Private Reports</b></li>
						<li><b><img width="16" height="16" src="<?php echo esc_url($path)?>/images/icon-stopwatch.svg" /> Priority Queue</b></li>
						<li><b><img width="16" height="16" src="<?php echo esc_url($path)?>/images/icon-thinking.svg" /> Automatic Monitoring</b></li>
						<li><b><img width="16" height="16" src="<?php echo esc_url($path)?>/images/icon-bell.svg" /> Alerts on Changes</b></li>
					</ul>
					<a class="button button-primary button-hero vb_subscribe" href="https://www.validbot.com/subscribe.php" target="_blank">Subscriptions...</a>
				</div>
			</div>
			
		</div>
		<?php
	}

	public function render_last_report($upgrade_notice) {
		$report = $this->base->get_last_report();
		//echo "<pre>";print_r($report);echo "</pre><br><br>";

		$grade = $this->get_grade($report['score']);
		$grade_domain = $this->get_grade($report['score_domain']);
		$grade_dns = $this->get_grade($report['score_dns']);
		$grade_server = $this->get_grade($report['score_server']);
		$grade_files = $this->get_grade($report['score_files']);
		$grade_page = $this->get_grade($report['score_page']);
		$grade_email = $this->get_grade($report['score_email']);
		
		if(empty($report)) { ?>
			<p>A validation report for <b><?php echo esc_html($this->site)?></b> was not found in your ValidBot account. Use the button below to start your first validation. Alternatively, you can visit your account at <a href="https://www.validbot.com">ValidBot.com</a> and initiate a report there.</p>

			<form action="<?php echo esc_url($this->get_page_url(array()))?>" method="post">
				<?php wp_nonce_field("run-tests") ?>
				<input type="submit" name="vb_runtests" class="button action button-primary button-hero" value="Validate My Website">
			</form>

			<br><br>

		<?php } else if($report['status']=="In Progress") { ?>
			<div class="vb_report_head">
				<div class="vb_report_head_grade">
					<div class="vb_grade vb_grade_u">?</div>
				</div>
				<div class="vb_report_head_txt">
					Report Date: <?php echo date('M j, Y @H:i',intval($report['created']))?><br>
					Test ID: <a href="https://www.validbot.com/report/<?php echo esc_html($report['id'])?>" target="_blank"><?php echo esc_html($report['id'])?></a>
				</div>
				<div class="vb_report_head_again">
					<b>Validation In Progress</b>
				</div>
			</div>

			<p>Your website is being validated right now. Please wait a few minutes and then reload the page.</p>
			<br><br>

		<?php } else { ?>
			<div class="vb_report_head">
				<div class="vb_report_head_grade">
					<div class="vb_grade vb_grade_<?php echo esc_html(strtolower(substr($grade,0,1)))?>"><?php echo esc_html($grade)?></div>
				</div>
				<div class="vb_report_head_txt">
					Report Date: <?php echo date('M j, Y @H:i',intval($report['created']))?><br>
					Test ID: <a href="https://www.validbot.com/report/<?php echo esc_html($report['id'])?>" target="_blank"><?php echo esc_html($report['id'])?></a>
				</div>
				<div class="vb_report_head_again">
					<?php if($upgrade_notice) { ?>
						Please wait to run a new report
					<?php } else { ?>
					<form action="<?php echo esc_url($this->get_page_url(array()))?>" method="post">
						<?php wp_nonce_field("run-tests") ?>
						<input type="submit" name="vb_runtests" class="button action" value="Validate My Site Again">
					</form>
					<?php } ?>
				</div>
			</div>

			<table class="wp-list-table widefat fixed striped table-view-list">
			<thead><tr>
				<th width="40"></th>
				<th width="20"></th>
				<th></th>
				<th width="120"></th>
			</tr></thead>
			<tbody class="vb_results">
				<tr>
					<td><div class="vb_grade vb_grade_<?php echo esc_html(strtolower(substr($grade_domain,0,1)))?>"><?php echo esc_html($grade_domain)?></div></td>
					<td colspan="2"><b>Domain Validation</b></td>
					<td><a href="https://www.validbot.com/report/<?php echo esc_html($report['id'])?>" target="_blank"><b>View Full Report</b></a></td>
				</tr>
				<?php $this->printTestBlock($report,'tests_domain') ?>
				<tr>
					<td><div class="vb_grade vb_grade_<?php echo esc_html(strtolower(substr($grade_dns,0,1)))?>"><?php echo esc_html($grade_dns)?></div></td>
					<td colspan="2"><b>DNS Validation</b></td>
					<td><a href="https://www.validbot.com/report/<?php echo esc_html($report['id'])?>" target="_blank"><b>View Full Report</b></a></td>
				</tr>
				<?php $this->printTestBlock($report,'tests_dns') ?>
				<tr>
					<td><div class="vb_grade vb_grade_<?php echo esc_html(strtolower(substr($grade_server,0,1)))?>"><?php echo esc_html($grade_server)?></div></td>
					<td colspan="2"><b>Server Validation</b></td>
					<td><a href="https://www.validbot.com/report/<?php echo esc_html($report['id'])?>" target="_blank"><b>View Full Report</b></a></td>
				</tr>
				<?php $this->printTestBlock($report,'tests_server') ?>
				<?php $this->printTestBlock($report,'tests_ssl') ?>
				<tr>
					<td><div class="vb_grade vb_grade_<?php echo esc_html(strtolower(substr($grade_files,0,1)))?>"><?php echo esc_html($grade_files)?></div></td>
					<td colspan="2"><b>Common Files Validation</b></td>
					<td><a href="https://www.validbot.com/report/<?php echo esc_html($report['id'])?>" target="_blank"><b>View Full Report</b></a></td>
				</tr>
				<?php $this->printTestBlock($report,'tests_favicon') ?>
				<?php $this->printTestBlock($report,'tests_manifest') ?>
				<?php $this->printTestBlock($report,'tests_robots') ?>
				<tr>
					<td><div class="vb_grade vb_grade_<?php echo esc_html(strtolower(substr($grade_page,0,1)))?>"><?php echo esc_html($grade_page)?></div></td>
					<td colspan="2"><b>Page Validation</b></td>
					<td><a href="https://www.validbot.com/report/<?php echo esc_html($report['id'])?>" target="_blank"><b>View Full Report</b></a></td>
				</tr>
				<?php $this->printTestBlock($report,'tests_page') ?>
				<?php $this->printTestBlock($report,'tests_meta') ?>
				<?php $this->printTestBlock($report,'tests_speed') ?>
				<tr>
					<td><div class="vb_grade vb_grade_<?php echo esc_html(strtolower(substr($grade_email,0,1)))?>"><?php echo esc_html($grade_email)?></div></td>
					<td colspan="2"><b>Email Validation</b></td>
					<td><a href="https://www.validbot.com/report/<?php echo esc_html($report['id'])?>" target="_blank"><b>View Full Report</b></a></td>
				</tr>
				<?php $this->printTestBlock($report,'tests_spf') ?>
				<?php $this->printTestBlock($report,'tests_dkim') ?>
				<?php $this->printTestBlock($report,'tests_dmarc') ?>
				<?php $this->printTestBlock($report,'tests_bimi') ?>
				<?php $this->printTestBlock($report,'tests_mx') ?>
			</tbody>
			</table>
		<?php
		}
	}

	private function printTestBlock($report,$block) {
		$path = plugin_dir_url(__FILE__).'../images/';

		foreach($report[$block] as $key => $test) {
			$id = intval(substr($key, 4));
			if(isset($report['tests'][$id])) {
				$desc = $report['tests'][$id];

				if($test['type']==1) {
					$icon = $path."icon-ok.svg";
				} else if($test['type']==2) {
					$icon = $path."icon-notok.svg";
				} else if($test['type']==3) {
					$icon = $path."icon-notice.svg";
				} else if($test['type']==4) {
					$icon = $path."icon-suggestion.svg";
				} else {
					$icon = $path."icon-unknown.svg";
				}

				?>
				<tr>
					<td></td>
					<td width="20" valign="middle"><img src="<?php echo esc_url($icon) ?>" width="20" height="20" class="vb_test_indicator" /></td>
					<td><?php echo esc_html($desc['title']) ?><br><?php echo esc_html($test['desc']) ?></td>
					<td><?php if(!empty($desc['has_more'])) { ?>
						<a href="https://www.validbot.com/tests/<?php echo intval($id) ?>/<?php echo esc_html($this->makeURLSlug($desc['title']))?>.html" target="_blank" class="vb_dimlink">Learn More...</a>
					<?php } ?></td>
				</tr>
				<?php
			}
		}
	}

	private function get_page_url($params) {
		$params['page']='validbot_admin';
		return add_query_arg($params,admin_url('admin.php'));
	}

	private function get_grade($score) {
		$grade = "F";

		if($score=="?") $grade = "?";

		else if($score>=97) $grade = "A+";	//7,8,9
		else if($score>=93) $grade = "A"; 	//3,4,5,6
		else if($score>=90) $grade = "A-"; 	//0,1,2

		else if($score>=87) $grade = "B+";	//7,8,9
		else if($score>=83) $grade = "B"; 	//3,4,5,6
		else if($score>=80) $grade = "B-"; 	//0,1,2

		else if($score>=77) $grade = "C+";	//7,8,9
		else if($score>=73) $grade = "C"; 	//3,4,5,6
		else if($score>=70) $grade = "C-"; 	//0,1,2

		else if($score>=67) $grade = "D+";	//7,8,9
		else if($score>=63) $grade = "D"; 	//3,4,5,6
		else if($score>=60) $grade = "D-"; 	//0,1,2

		return $grade;
	}

	private function makeURLSlug($name) {
		if(!empty($name)) {
			$name = preg_replace("/[^a-zA-Z0-9]/"," ",$name); //replace all special chars with spaces
			$name = preg_replace("!\s+!","-",$name); //replace all strings of spaces with a single -
			$name = strtolower(trim($name,'-')); //trim leading and trailing dashes
			return $name;
		}
		return "";
	}
}

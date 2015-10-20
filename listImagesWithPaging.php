<?php
// List all the images in the KMC, a "page" at a time
//
// BUGS: Used $entry->dataUrl (original uploaded video) because $entry->downloadUrl doesn't work
// for the default samples in the SaaS accounts.....!
// Where are the downloadUrls for the default samples in the account?
// Your Kaltura partner credentials
define("PARTNER_ID", "____________");
define("ADMIN_SECRET", "____________________________");
// define("USER_SECRET",  "zzzzzzzzzzzzzzzzzzzzzzzzzz");
require_once "php5/KalturaClient.php";
require_once "dbconnect.php";
require_once "functions-image.php";
$db = dbconn();
$user = "etc_full_staff";
$kconf = new KalturaConfiguration(PARTNER_ID);
// If you want to use the API against your self-hosted CE,
// go to your KMC and look at Settings -> Integration Settings to find your partner credentials
// and add them above. Then insert the domain name of your CE below.
// $kconf->serviceUrl = "http://www.mySelfHostedCEsite.com/";
$kclient = new KalturaClient($kconf);
$ksession = $kclient->session->start(ADMIN_SECRET, $user, KalturaSessionType::ADMIN, PARTNER_ID);
if (!isset($ksession)) {
	die("Could not establish Kaltura session. Please verify that you are using valid Kaltura partner credentials.");
}
$kclient->setKs($ksession);
// Set the response format
// KALTURA_SERVICE_FORMAT_JSON  json
// KALTURA_SERVICE_FORMAT_XML   xml
// KALTURA_SERVICE_FORMAT_PHP   php
$kconf->format = KalturaClientBase::KALTURA_SERVICE_FORMAT_PHP;
$kfilter = new KalturaMediaEntryFilter();
$kfilter->mediaTypeEqual = KalturaMediaType::IMAGE;
// Make sure video is done transcoding or whatever
$kfilter->status = KalturaEntryStatus::READY;
// List in descending order
$kfilter->orderBy = KalturaBaseEntryOrderBy::CREATED_AT_DESC;
// $kfilter->orderBy = KalturaBaseEntryOrderBy::CREATED_AT_ASC;

if (isset($_POST['category'])) {
	$qcat = "";
	foreach ($_POST['category'] as $getcat) {
		$qcat .= $getcat . ",";
		$kfilter->categoryAncestorIdIn = $qcat;
	}
}

if (isset($_POST['search']) && !empty($_POST['search'])) {
	$search = $_POST['search'];
	$kfilter->freeText = $search;
}

// Create pager
$pager = new KalturaFilterPager();
// choose the pageSize -- number of items per call
// choose the pageIndex -- which page we're on now (page "1" is the first page)
$pager->pageSize = 500;
$pager->pageIndex = 1;

$catfilter = new KalturaCategoryFilter();
$catfilter->status = KalturaCategoryStatus::ACTIVE;
$catfilter->orderBy = KalturaBaseEntryOrderBy::NAME_ASC;
$catfilter->depthEqual = 0;

?>

<head>
	<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="http://corp.kaltura.com/sites/default/files/favicon%20%289%29_0.ico">
	<title>CIDDE Kaltura</title>
	<link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body>
	<header id="header">
		<div class="inner">
			<a href="/" id="logo">
				<img src="img/logo.png">
			</a>
			<div id="header-main-nav">
				<ul class="nav">
					<li class="nav-li">
						<a href="listVideosWithPaging.php" class="nav-link">Video</a>
					</li>
					<li class="nav-li">
						<a href="listAudiosWithPaging.php" class="nav-link">Audio</a>
					</li>
					<li class="nav-li-selected">
						<a href="listImagesWithPaging.php" class="nav-link">Image</a>
					</li>
				</ul>
			</div>
		</div>
	</header>
	<hr>
	<div id="categories">
		<form action="" method="post" id="form">
			
			<?php
				if (isset($_POST['categoryall'])) {	
				?> <input type="checkbox" name="categoryall[]" value="all" checked  onclick="check()" > <?php
				} elseif (isset($_POST['category'])) { ?>
					<input type="checkbox" name="categoryall[]" value="all" onclick="check()">
					<?php 
				} else { ?>
					<input type="checkbox" name="categoryall[]" value="all" checked onclick="check()">
				<?php
				}
			?>

			<script type="text/javascript">
				function check() {
					if (document.getElementsByName('categoryall[]')[0].checked) {
						var length = document.getElementsByName('category[]').length;
						for (var i = length - 1; i >= 0; i--) {
							document.getElementsByName('category[]')[i].checked = false;
						};
					}
					document.forms["form"].submit();
				}

			</script>
			
			<label><b>All Categories</b></label><br>
		<?php 
			$cats = $kclient->category->listAction($catfilter);
		?>
		
		<?php 
			if (!empty($cats->objects)) {
				foreach ($cats->objects as $cat) {
					?>							
					<input type="checkbox" name="category[]" value="<?php echo $cat->id; ?>" onclick="togglecheck()"   
					<?php
					if (isset($_POST['category'])) {
						?>
						<?php
						foreach ($_POST['category'] as $catselect) {
							if ($catselect == $cat->id) {
								echo "checked";
								break;
							}
						}
					} 
					echo ">";
						
					?>
					
					<label><?php echo $cat->name ?></label>
					<script type="text/javascript">
						function togglecheck() {
							document.getElementsByName('categoryall[]')[0].checked = false;
							document.forms["form"].submit();
						}
					</script>
					<br>
					<?php
				}
			}
		?>
	</div>
	<div id="content">
		
<?php
$result = $kclient->media->listAction($kfilter, $pager);
$count = $result->totalCount; // total number of items in the account
?>
		<div id="stat">
			<div id="total">
				<p>Total: <?php echo $result->totalCount ?> Images 
				<?php 
					if (isset($_POST['category'])) {
						$cfilter = new KalturaCategoryFilter();
						echo " in ";
						for ($i=0; $i < count($_POST['category']); $i++) { 
							$cfilter->idEqual = $_POST['category'][$i];
							$cname = $kclient->category->listAction($cfilter);
							foreach ($cname->objects as $catobj) {
								$catname = $catobj -> name;
								echo "<b><i>" . $catname . "</i></b>";
							}
							if ($i !== count($_POST['category']) - 1) {
								echo ", ";
							}
						}
					}

					if (isset($_POST['search']) && !empty($_POST['search'])) {
						echo " with keyword <b><i>" . $_POST['search'] . "</i></b>";
					}
				?>
				</p>
			</div>
			<div id="search">
					<input type="text" placeholder="Search Entries" name="search" 
					<?php	
						if (isset($_POST['search']) && !empty($_POST['search'])) {
							echo "value='" . $_POST['search'] . "'";
						}
					?>
					/>
					<input type="submit" value="Search" name="s"/>
				</form>
			</div>
		</div>

<?php
	while (!empty($result->objects)) {
		?>
		<div class="page">
			<div id='page<?php echo $pager->pageIndex ?>'>
				<div class="pageid">
					Page <?php echo $pager->pageIndex ?>
				</div>
			</div>
			<div class="records">
				<table>
					<thead>
						<th class="No">No.</th>
						<!-- <th>Media Type</th> -->
						<th class="entryid">Entry ID</th>
						<th>Title</th>
						<th class="creator">Creator</th>
						<th>Tags</th>
						<th>Categories</th>
						<th class="url">Download</th>
					</thead>
					<tbody>
						<?php
						$order = 0;
						foreach ($result->objects as $entry) {
							$order++;
							// if (!findrecord($entry->id, $db)) {
							// 	insertrecord($entry->id, $entry->name, $entry->description, $entry->userId, $entry->tags, $entry->categories, $entry->downloadUrl, $db);
							// }
							// echo '<tr><td><img src="'.$entry->thumbnailUrl.'">&nbsp;&nbsp;
					  //   Title: '.$entry->name.'&nbsp;&nbsp;
					  //    <a href="'.$entry->downloadUrl.'">download</a>&nbsp;&nbsp;
					  //    Created on: '.date("D M j G:i:s T Y", $entry->createdAt).'</td></tr>';
					     	echo "<tr>";
					     	echo "<td class='No'>$order</td>";
					     	// echo "<td><img src='" . $entry->thumbnailUrl . "'></td>";
					     	// echo "<td>" . $entry->mediaType . "</td>";
					     	echo "<td class='entryid'>" . $entry->id . "</td>";
					     	echo "<td>" . $entry->name . "</td>";
					     	echo "<td class='creator'>" . $entry->userId . "</td>";
					     	echo "<td>" . $entry->tags . "</td>";
					     	echo "<td>" . $entry->categories . "</td>";
					     	echo "<td class='url'><a href='" . $entry->downloadUrl . "' >Download</a></td>";
					     	echo "</tr>";
						}
						?>
					</tbody>
					<thead>
						<th class="No">No.</th>
						<!-- <th>Media Type</th> -->
						<th class="entryid">Entry ID</th>
						<th>Title</th>
						<th class="creator">Creator</th>
						<th>Tags</th>
						<th>Categories</th>
						<th class="url">Download</th>
					</thead>
				</table>
				<?php
				$pager->pageIndex++;
				$result = $kclient->media->listAction($kfilter, $pager);
				?>
			</div>
		</div>
		<?php
	}
?>
	</div>

	<footer id="footer">
		<hr>
		<p>&copy; Copyright Yan Ma &amp; mayangithub</p>

	</footer>
</body>
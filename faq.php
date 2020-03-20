<?php 
//include files for the script to run
require_once 'includes/app.inc.php';
require_once 'includes/settings.inc.php';
require_once 'vendor/autoload.php';
set_include_path(get_include_path() . ':libs');
require_once 'db.class.inc.php';
require_once 'mail.inc.php';
require_once 'orders.inc.php';
require_once 'paperTypes.inc.php';
require_once 'finishOptions.inc.php';
require_once 'posterTube.inc.php';
require_once 'rushOrder.inc.php';


?>

<HTML>
<HEAD>
<meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="includes/stylesheet.css"
                media='screen'>

                <script type="text/javascript" src="includes/poster.inc.js"></script>

                <title>Poster Printer - Frequently Asked Questions</title>

</head>

<body OnLoad="document.posterInfo.posterWidth.focus();">
        <div class='order_container'>
                <div class="order">
                        <h2>Poster Printer - Frequently Asked Questsions</h2>


<hr>
<h3>Which form of payments do you accept?</h3>
<p>We only accept CFOPs as payment.  We are not able to accept credit cards, checks or cash.  A CFOP is the University's Account Number.  
Please contact your advisor or the business office if you do not know which CFOP you should use.</p>
<h3>Which filetypes do you accept?</h3>
<p>We accept all file types but strongly recommend PDFs. The most popular are PDFs and PPTX (Powerpoint).</p>
<h3>Which file format is best?</h3>
<p>PDFs are by far the best file format.  They produce the best accuracy.</p>
<h3>Can I have my poster printed using different dimensions than the poster file is set at?</h3>
<p>The poster file length and width should be the same as the length and width you submitted the order at.  If we were to print it using different dimensions, pixelation and distrortions will occur. </p>
<h3>Why did the width and length of my poster get flipped in my order?</h3>
<p>We automatically flipped the width and length to maximize the use of the paper you select.  This way we use less paper and it will save you money.  Your poster's printed orientation will remain the same</p>
<h3>How long will it take to print my poster?</h3>
<p>For a standard order, we guarantee within <strong>72 business hours (3 Days)</strong>.  This excludes weekends, holidays and breaks.</p>
<h3>How long does a rush order take?</h3>
<p>For a rush order, we guarantee within <strong>24 business hours (1 Day)</strong>.  We will also make a best faith effort to get it done
sooner if need be.  This excludes weekends, holidays and breaks.</p>
<h3>Can you print multiple copies of the same poster?</h3>
<p>If you want multiple copies of the poster, please submit an order for each copy.</p>
<h3>Can I get a proof of my poster?</h3>
<p>If you want a proof of your poster, please submit an order with the dimensions you want the proof to be printed at.  Once you receive the proof and like the results, submit another order with the correct dimensions for your final poster.</p>
<h3>I have another question that wasn't answered here</h3>
<p>Please email us at <a href='mailto:'<?php echo admin_email; ?>'><?php echo admin_email; ?></a> and we will get back to you.</p>
<div class='row justify-content-center'>
<table class='center'>
	<td><a href='index.php'>Submit Order</a></td>
</table>
</div>
   </div>
        </div>



<?php require_once 'includes/footer.inc.php'; ?>


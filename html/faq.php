<?php 
require_once 'includes/main.inc.php';
require_once 'includes/header.inc.php'; 

?>
<h2>Frequently Asked Questions</h2>
<hr>
<h4>What form of payments do you accept?</h4>
<p>We only accept CFOPs as payment.  We are not able to accept credit cards, checks or cash.  A CFOP is the University's Account Number.  
Please contact your advisor or the business office if you do not know what CFOP you should use.</p>
<h4>What filetypes do you accept?</h4>
<p>We accept the following filetypes.</p>
<h4>What file format is best?</h4>
<p>PDFs are by far the best file format.  They produce the best accuracy.</p>
<h4>Why did the width and length of my poster get flipped in my order?</h4>
<p>We automatically flipped the width and length to maximize the use of the paper you select.  This way we use less paper and save you money.  Your poster's orientation will remain the same</p>
<h4>How long will it take to print my poster?</h4>
<p>For a standard order, we gurantee within <?php echo settings::get_order_timeframe(); ?> business hours.  This excludes weekends, holidays and breaks.</p>
<h4>How long does a rush order take?</h4>
<p>For a rush order, we gurantee within <?php echo settings::get_rush_order_timeframe(); ?> business hours.  We will also make a best faith effort to get it done
sooner if need be.  This excludes weekends, holidays and breaks.</p>
<h4>Can you print multiple copies of the same poster?</h4>
<p>If you want multiple copies of the poster, please submit an order for each copy.</p>
<h4>Can I get a proof of my poster?</h4>
<p>If you want a proof of your poster, please submit an order with the dimensions you want the proof to be printed at.  Once you receive the proof and like the results, submit another order with the correct dimensions for your final poster.</p>
<h4>I have another question that wasn't answered here</h4>
<p>Please email us at <a href='mailto:<?php echo settings::get_admin_email(); ?>'><?php echo settings::get_admin_email(); ?></a> and we will answer your question.</p>
<div class='row justify-content-center'>
<p><a class='btn btn-primary' href='index.php'>Go Back</a></p>
</div>



<?php require_once 'includes/footer.inc.php'; ?>

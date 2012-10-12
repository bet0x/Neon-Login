<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{%?PanelTitle} - {%?PageTitle}</title>
    <link rel="stylesheet" type="text/css" href="templates/blue_default/style/reset.css" /> 
    <link rel="stylesheet" type="text/css" href="templates/blue_default/style/root.css" /> 
    <link rel="stylesheet" type="text/css" href="templates/blue_default/style/grid.css" /> 
    <link rel="stylesheet" type="text/css" href="templates/blue_default/style/typography.css" /> 
    <link rel="stylesheet" type="text/css" href="templates/blue_default/style/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="templates/blue_default/style/jquery-plugin-base.css" />
	<link rel="stylesheet" type="text/css" href="templates/blue_default/style/basic.css" />
    <!--[if IE 7]>	  <link rel="stylesheet" type="text/css" href="templates/blue_default/style/ie7-style.css" />	<![endif]-->
	<!--[if lt IE 7]> <link type='text/css' href='css/basic_ie.css' rel='stylesheet' media='screen' /> <![endif]-->
	<script type="text/javascript" src="templates/blue_default/js/jquery.min.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery-ui-1.8.11.custom.min.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery-settings.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/toogle.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.tipsy.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.uniform.min.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.wysiwyg.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/raphael.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/analytics.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/popup.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/fullcalendar.min.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.prettyPhoto.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.ui.core.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.ui.mouse.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.ui.widget.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.ui.slider.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.ui.datepicker.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.ui.tabs.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.ui.accordion.js"></script>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.simplemodal.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/basic.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.dataTables.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/jquery.form.js"></script>
	<script type="text/javascript" src="templates/blue_default/js/ajaxsubmit.js"></script>
</head>
<body>
<div class="wrapper">
	<!-- START HEADER -->
    <div id="header">
    	<!-- logo -->
    	<div class="logo">	<a href="main.php"><img src="templates/blue_default/img/logo.png" width="112" height="35" alt="logo"/></a>	</div>

        <!-- notifications -->
        <div id="notifications">
        	<a href="index.php" class="qbutton-left"><img src="templates/blue_default/img/icons/header/dashboard.png" width="16" height="15" alt="Dashboard" /></a>
        	<a href="#" class="qbutton-normal tips"><img src="templates/blue_default/img/icons/header/message.png" width="19" height="13" alt="News" /></a>
        	<a href="#" class="qbutton-right"><img src="templates/blue_default/img/icons/header/graph.png" width="19" height="13" alt="support" /></a>
          <div class="clear"></div>
        </div>

        <!-- profile box -->
        <div id="profilebox">
        	<a href="#" class="display">
            	<img src="templates/blue_default/img/simple.jpg" width="33" height="33" alt="profile"/>	<b>Logged in as</b>	<span>{%?Username}</span>
            </a>
            
            <div class="profilemenu">
            	<ul>
                	<li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
            
        </div>
        
        
        <div class="clear"></div>
    </div>
    <!-- END HEADER -->
    <!-- START MAIN -->
    <div id="main">
        <!-- START SIDEBAR -->
        <div id="sidebar">
        	
            <!-- start searchbox -->
            <div id="searchbox">
            	<div class="in">
               	  <form id="form1" name="form1" method="post" action="">
                  	<input name="textfield" type="text" class="input" id="textfield" onfocus="$(this).attr('class','input-hover')" onblur="$(this).attr('class','input')"  />
               	  </form>
            	</div>
            </div>
            <!-- end searchbox -->
            
            <!-- start sidemenu -->
            <div id="sidemenu">
            	<ul>
                	<li{%if PageName == main} class="active"{%/if}><a href="index.php"><img src="templates/blue_default/img/icons/sidemenu/laptop.png" width="16" height="16" alt="icon"/>Dashboard</a></li>
                </ul>
            </div>
            <!-- end sidemenu -->
            
        </div>
        <!-- END SIDEBAR -->

        <!-- START PAGE -->
        <div id="page">
		{%?Content}
		</div>
		
    <div class="clear"></div>
    </div>
    <!-- END MAIN -->
    <!-- START FOOTER -->
    <div id="footer">
    	<div class="left-column">{%?PanelTitle}</div>
        <div class="right-column">&copy Copyright 2012 - <a href="http://neonpanel.com" target="_blank">NEON</a> - All rights reserved.</div>
    </div>
    <!-- END FOOTER -->

</div>
</body>
</html>
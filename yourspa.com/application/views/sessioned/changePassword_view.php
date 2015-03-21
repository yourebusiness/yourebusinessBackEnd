<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#"><img src="../../../images/spa_logo.png"></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li><a href="<?php echo base_url("admin"); ?>"><span class="glyphicon glyphicon-home"></span> Home <span class="sr-only">(current)</span></a></li>
        <li class="dropdown active">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-globe"></span> Modules <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li class="dropdown-submenu">
                <a tabindex="-1" href="#"><span class="glyphicon glyphicon-folder-close"></span> Administrations </a>
                <ul class="dropdown-menu">
                  <li><a tabindex="-1" href="#"></a></li>
                  <li><a href="<?php echo base_url("admin/masseur"); ?>"><span class="glyphicon glyphicon-user"></span> Masseurs </a></li>
                  <li><a href="<?php echo base_url("admin/users"); ?>"><span class="glyphicon glyphicon-star-empty"></span> Users</a></li>
                </ul>
            </li>
            <li class="divider"></li>
            <li class="dropdown-submenu">
                <a tabindex="-1" href="#"><span class="glyphicon glyphicon-wrench"></span> Services </a>
                <ul class="dropdown-menu">
                  <li><a tabindex="-1" href="#"></a></li>
                  <li><a href="<?php echo base_url("admin/services"); ?>"><span class="glyphicon glyphicon-list"></span> List of Services</a></li>
                  <li><a href="<?php echo base_url("admin/addService_view"); ?>"><span class="glyphicon glyphicon-plus-sign"></span> Add Services</a></li>
                </ul>
            </li>

            <li class="dropdown-submenu">
              <a tabindex="-1" href="#"><span class="glyphicon glyphicon-usd"></span> Transactions </a>
                <ul class="dropdown-menu">
                  <li><!-- <a tabindex="-1" href="#"></a> --></li>
                  <li><a href="#"><span class="glyphicon glyphicon-plus-sign"></span> Add new Transaction</a></li>
                  <!-- <li><a href=""><span class="glyphicon glyphicon-usd"></span> --- </a></li> -->
                </ul>
            </li>
            <li class="divider"></li>
            <li class="dropdown-submenu">
              <a tabindex="-1" href="#"><span class="glyphicon glyphicon-file"></span> Reports</a>
              <ul class="dropdown-menu">
                <li><a tabindex="-1" href="#"></a></li>
                    <li><a href="#"><span class="glyphicon glyphicon-user"></span> Per Masseur</a></li>
                    <li><a href="#"><span class="glyphicon glyphicon-usd"></span> Per Services</a></li>
              </ul>
            </li>

          </ul>
        </li>
      </ul>
      
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" style="color: blue;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-user"></span> <?php echo $username; ?> <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="#"><span class="glyphicon glyphicon-user"></span> Profile</a></li>
            <li><a href="<?php echo base_url("api/logout"); ?>"><span class="glyphicon glyphicon-log-out"></span> Log-out</a></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<div class="container">
    <h3>Change Password</h3> <hr />

    <div class="row">
      <div class="col-sm-8">
        <div class="col-sm-4"></div>
        <div class="col-sm-4">

          <div class="alert alert-danger alert-block fade in" id="dangerAlert">
            <h4>Error!!!</h4>
            <p>All fields must be provided.</p>
          </div>
          <form class="form-horizontal" method="post">
            <input type="hidden" id="userId" name="userId" value="<?php echo $userId; ?>" />
            <div class="form-group">
              <input class="form-control" type="password" id="oldPassword" placeholder="Old password here" />
            </div>
            <div class="form-group">
              <input class="form-control" type="password" id="newPassword" placeholder="New password here" />
            </div>
            <div class="form-group">
              <input class="form-control" type="password" id="confirmPassword" placeholder="confirm password here" />
            </div>
            <div class="form-group">
              <button type="submit" id="updatePassword" class="btn btn-primary">Update</button>
              <button type="button" id="cancel" class="btn btn-default" onclick="window.location.href='<?php echo base_url("admin"); ?>'">Cancel</button>
            </div>
          </form>
        </div>

        <div class="col-sm-4"></div>
      </div>

      <div class="col-sm-4"></div>
    </div>
</div>
    
    <!-- If no online access, fallback to our hardcoded version of jQuery -->
    <script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-1.11.2.min.js"><\/script>')</script> 
    
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    
    <script src="http://yourspa.com/includes/js/changePassword.js"></script>
    
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mustache.js/0.8.1/mustache.js"></script>
    
</body>
</html>
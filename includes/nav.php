
   <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">Medivac</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="index.php">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
            <?php if(!logged_in()): ?>
            <li><a href="login.php">Log-in</a></li>
            <?php endif; ?>
            <?php if(logged_in()): ?>
              <li><a href="logout.php">Log-out</a></li>
            <?php endif; ?>
            
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
  

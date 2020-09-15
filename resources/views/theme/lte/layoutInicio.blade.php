<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Acuasur</title>
        <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico" />
        <!-- Font Awesome icons (free version)-->
        <script src="https://use.fontawesome.com/releases/v5.13.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
         <!-- Theme Custom -->
  <link rel="stylesheet" href="{{asset("assets/css/styles.css")}}">
  
  
        
    </head>
    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand js-scroll-trigger" href="http://aquaprogrammer.com" target="_blank">Aquaprogrammer</a>
                <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#about">Acerca de Acuasur Rural</a></li>
                        <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#projects">Historia</a></li>
                        <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#signup">Contacto</a></li>
                        <li class="nav-item"><a class="nav-link js-scroll-trigger" href="{{route('login')}}" >Hola, {{Session()->get('usuario') ?? 'Invitado'}}</a></li>
                    </ul>
                </div>      
            </div>
        </nav>
        <!-- Masthead-->
        <header class="masthead">
            <div class="container d-flex h-100 align-items-center">
                <div class="mx-auto text-center">
                    <img src="assets/img/logo_acuasur.jpeg" alt="User Avatar" class="col-xs-12 col-sm-12 col-md-12 m-t-10 text-center img-size-500 mr-12" />
                    <!--<h1 class="mx-auto my-0 text-uppercase">Acuasur Rural</h1>-->
                    <h2 class="text-white-50 mx-auto mt-2 mb-5">Administraccion y programación de Toma de Lectura</h2>
                    <a class="btn btn-primary js-scroll-trigger" href="{{route('login')}}">Login lectura</a>
                </div>
            </div>
        </header>
        <!-- About-->
        <section class="about-section text-center" id="about">
            <div class="container d-flex h-100 align-items-center">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <h2 class="text-white mb-4">Acuasur Rural</h2>
                        <p class="text-white-50 text-justify mb-0">
                            El acueducto de Acuasur abastece a 12 localidades rurales entre corregimientos
                            y veredas del municipio de Jamundí, Valle del Cauca. El corregimiento que más
                            usuarios tiene es Robles que está ubicado a 24 kilómetros de Cali   
                            <!--<a href="https://startbootstrap.com/template-overviews/grayscale/">the preview page</a>-->
                            
                        </p>
                    </div>
                </div>
                <div class="image-shadow col-lg-7 mx-auto">
                <img class="img-fluid" src="assets/img/ipad.png" alt="" />
                </div>  
            </div>
        </section>
        <!-- Projects-->
        <section class="projects-section bg-light" id="projects">
            <div class="container">
                <!-- Featured Project Row-->
                <div class="row align-items-center no-gutters mb-4 mb-lg-5">
                    <div class="col-xl-4 col-lg-4"><img class="img-fluid mb-3 mb-lg-0" src="assets/img/Robles.jpg" alt="" /></div>
                    <div class="bg-white col-xl-8 col-lg-8">
                        <div class="featured-text text-center text-lg-left">
                           <h4 class="text-black">Historia</h4>
                            <p class="text-black-50 text-justify mb-2">A partir de 1939 la zona urbana de Robles comenzó a ser abastecida por un
                                sistema de distribución de agua que tenía una pequeña bocatoma. Después se con bombeo que tampoco
                                resolvió el problema del agua.
                                Salvo Timba todas las demás veredas consumían agua cruda superficial o subterránea reflejándose en la gran cantidad
                                de enfermedades gastrointestinales en la población infantil. Las comunidades de Villapaz, Qinamayó y Robles se unieron a través de sus
                                líderes y algunos políticos para dar una solución definitiva. En 1992 se consiguieron recursos para el estudio y diseño de una solución global
                                proponiendo diferentes fuentes como agua subterránea o el río Guachinte para finalmente decidirse por el río Timba que garantizaría continuidad en cantidad y
                                calidad. En el año 1993 se consiguieron recursos a través del Fondo Nacional de Solidaridad (Acuasur, 2007). En 1994 inició la construcción del acueducto
                                incorporando 12 sistemas locales. La sociedad de acueductos y alcantarillados del Valle del Cauca ACUAVALLE ESP realizó la interventoría del proyecto.</p>
                                <hr class="d-none d-lg-block mb-1 ml-1" />
                        </div>
                    </div>
                </div>
                <!-- Project One Row-->
                <div class="row justify-content-center no-gutters mb-5 mb-lg-0">
                    <div class="col-lg-4"><img class="img-fluid " src="assets/img/Bocatoma.JPG" alt="" /></div>
                    <div class="col-lg-8">
                        <div class="bg-white text-center h-100 project">
                            <div class="d-flex h-100">
                                <div class="project-text w-100 my-auto text-center text-lg-left">
                                    <h4 class="text-black">Fuente de agua bocatoma</h4>
                                    <p class="mb-0 text-black-50">La fuente de agua es el río Timba que tiene un caudal aproximado de 3000L/s. La actividad económica principal es la agricultura con cultivos de caña de azúcar, arroz, cítricos y plátano</p>
                                    <hr class="d-none d-lg-block mb-0 ml-0" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Project Two Row-->
                <div class="row justify-content-center no-gutters">
                    <div class="col-lg-3"><img class="img-fluid" src="assets/img/red.jpg" alt="" /></div>
                    <div class="col-lg-4"><img class="img-fluid" src="assets/img/red1.jpg" alt="" /></div>
                    <div class="col-lg-5 order-lg-first">
                        <div class="bg-white text-center h-100 project">
                            <div class="d-flex h-100">
                                <div class="project-text w-100 my-auto text-center text-lg-right">
                                    <h4 class="text-black">Distribucción de tuberia</h4>
                                    <p class="mb-0 text-black-50">Red de distribucción de acueducto</p>
                                    <hr class="d-none d-lg-block mb-0 mr-0" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Signup
        <section class="signup-section" id="signup">
            <div class="container">
                <div class="row">
                    <div class="col-md-10 col-lg-8 mx-auto text-center">
                        <i class="far fa-paper-plane fa-2x mb-2 text-white"></i>
                        <h2 class="text-white mb-5">Escribenos para recibir información!</h2>
                        <form class="form-inline d-flex">
                            <input class="form-control flex-fill mr-0 mr-sm-2 mb-3 mb-sm-0" id="inputEmail" type="email" placeholder="Ingresa tu email..." />
                            <button class="btn btn-primary mx-auto" type="submit">Escribenos</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>-->
        <!-- Contact-->
        <section class="contact-section bg-black" id="signup">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-map-marked-alt text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Dirección</h4>
                                <hr class="my-4" />
                                <div class="small text-black-50">Cali, Colombia</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-clock text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Horarios (Emergencia covid-19)</h4>
                                <hr class="my-4" />
                                <div class="small text-black-50">Lunes a viernes de:<p>7:00 A.M a 2:00 P.M</p> </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Escribenos al Email</h4>
                                <hr class="my-4" />
                                <div class="small text-black-50"><a href="#!">acuasurj@hotmail.com</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-mobile-alt text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Linea de atención al cliente y <i class="fab fa-whatsapp-square" style='font-size:18px;color:green'></i></h4>
                                <hr class="my-4" />
                                <div class="small text-black-50">+57 3218781671</div>
                               </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="social d-flex justify-content-center">
                    <a class="mx-2" href="#!"><i class="fab fa-twitter"></i></a>
                    <a class="mx-2" href="#!"><i class="fab fa-facebook-f"></i></a>
                    <!--<a class="mx-2" href="#!"><i class="fab fa-github"></i></a>-->
                </div>
            </div>
        </section>
        <!-- Footer-->
        
        <footer class="footer bg-black small text-center text-white-50"><div class="container"><strong>Copyright &copy; 2019-2020 <a href=#>Aqua Programmer<i class="fa fa-tint"></i></a>.</strong> All rights
    reserved.</div></footer>
        <!-- Bootstrap core JS-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
        <!-- Third party plugin JS-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
        <!-- Core theme JS-->
        
        <script src="{{asset("assets/js/scriptsInicio.js")}}"></script>
    </body>
</html>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Soluciones de diseño de sistemas de información para el sector de servicios públicos. Optimiza la gestión de acueductos, alcantarillados y más." />
        <meta name="author" content="Aqua Programmer" />
        <title>Aqua Programmer - Soluciones de Sistemas para Servicios Públicos</title>
        <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico" />
        <script src="https://use.fontawesome.com/releases/v5.13.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet" />
        <link rel="stylesheet" href="{{asset("assets/css/styles.css")}}">
    </head>
    <body id="page-top">
        <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand js-scroll-trigger" href="#page-top">Aqua Programmer</a>
                <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#about">Nosotros</a></li>
                        <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#services">Servicios</a></li>
                        <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#portfolio">Proyectos</a></li>
                        <li class="nav-item"><a class="nav-link js-scroll-trigger" href="#contact">Contacto</a></li>
                        @if(Session()->has('usuario'))
                            <li class="nav-item"><a class="nav-link js-scroll-trigger" href="{{route('login')}}" >Hola, {{Session()->get('usuario')}}</a></li>
                        @else
                            <li class="nav-item"><a class="nav-link js-scroll-trigger" href="{{route('login')}}" >Ingresar</a></li>
                        @endif
                    </ul>
                </div>  
            </div>
        </nav>
        <header class="masthead">
            <div class="container d-flex h-100 align-items-center">
                <div class="mx-auto text-center">
                    <h1 class="mx-auto my-0 text-uppercase">Aqua Programmer</h1>
                    <h2 class="text-white-50 mx-auto mt-2 mb-5">Innovación en Sistemas de Información para Servicios Públicos</h2>
                    <a class="btn btn-primary js-scroll-trigger" href="#services">Descubre Nuestras Soluciones</a>
                </div>
            </div>
        </header>

        <section class="about-section text-center" id="about">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <h2 class="text-white mb-4">Transformando la Gestión de Servicios Públicos</h2>
                        <p class="text-white-50 text-justify mb-0">
                            En **Aqua Programmer**, somos expertos en el diseño y desarrollo de **sistemas de información personalizados** para optimizar la operación y administración de empresas del sector de servicios públicos. Entendemos los desafíos únicos que enfrentan y ofrecemos soluciones tecnológicas robustas que mejoran la eficiencia, la transparencia y la calidad del servicio a los ciudadanos.
                            <br><br>
                            Nuestra experiencia se centra en la digitalización de procesos clave, desde la gestión de lecturas y facturación hasta el control de infraestructuras y la atención al cliente, impulsando la transformación digital de su organización.
                        </p>
                    </div>
                </div>
                 <img class="img-fluid mt-5" src="assets/img/ipa.png" alt="Sistemas de Información para Servicios Públicos" />
            </div>
        </section>

        <section class="projects-section bg-light" id="services">
            <div class="container">
                <div class="row align-items-center no-gutters mb-5">
                    <div class="col-xl-6 col-lg-6">
                        <img class="img-fluid mb-3 mb-lg-0" src="assets/img/service_gestion.jpg" alt="Gestión de Acueductos y Alcantarillados" />
                    </div>
                    <div class="bg-white col-xl-6 col-lg-6">
                        <div class="featured-text text-center text-lg-left p-4">
                            <h4 class="text-black">Gestión Integral de Acueductos y Alcantarillados</h4>
                            <p class="text-black-50 text-justify mb-0">
                                Desarrollamos sistemas para el monitoreo en tiempo real, gestión de redes, control de calidad del agua y optimización de la distribución, asegurando un servicio eficiente y sostenible.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center no-gutters mb-5">
                     <div class="bg-white col-xl-6 col-lg-6 order-lg-2">
                        <img class="img-fluid mb-3 mb-lg-0" src="assets/img/service_lectura.jpg" alt="Sistemas de Toma de Lectura y Facturación" />
                    </div>
                    <div class="bg-white col-xl-6 col-lg-6 order-lg-1">
                        <div class="featured-text text-center text-lg-right p-4">
                            <h4 class="text-black">Sistemas de Toma de Lectura y Facturación</h4>
                            <p class="text-black-50 text-justify mb-0">
                                Implementamos soluciones móviles y web para la toma de lecturas, con integración directa a sistemas de facturación y gestión de clientes, minimizando errores y agilizando procesos.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center no-gutters">
                    <div class="col-xl-6 col-lg-6">
                        <img class="img-fluid mb-3 mb-lg-0" src="assets/img/service_mantenimiento.jpg" alt="Mantenimiento y Atención al Cliente" />
                    </div>
                    <div class="bg-white col-xl-6 col-lg-6">
                        <div class="featured-text text-center text-lg-left p-4">
                            <h4 class="text-black">Plataformas de Mantenimiento y Atención al Cliente</h4>
                            <p class="text-black-50 text-justify mb-0">
                                Creamos módulos para la planificación y seguimiento de mantenimientos, así como portales de autogestión y CRM para una atención eficiente y personalizada a sus usuarios.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="projects-section bg-black" id="portfolio">
            <div class="container">
                <h2 class="text-white text-center mb-5">Nuestros Proyectos Destacados</h2>
                <div class="row justify-content-center no-gutters mb-5 mb-lg-0">
                    <div class="col-lg-6">
                        <img class="img-fluid" src="assets/img/project_billing.jpg" alt="Sistema de Gestión de Clientes y Facturación" />
                    </div>
                    <div class="col-lg-6">
                        <div class="bg-white text-center h-100 project">
                            <div class="d-flex h-100">
                                <div class="project-text w-100 my-auto text-center text-lg-left">
                                    <h4 class="text-black">Sistema de Gestión de Clientes y Facturación (Ejemplo "Acuasur Rural")</h4>
                                    <p class="mb-0 text-black-50">
                                        Desarrollamos una plataforma integral para la administración de usuarios, toma de lecturas móvil y generación de facturas, mejorando la eficiencia operativa del acueducto rural de Jamundí, Valle del Cauca. (Este es tu caso actual, puedes adaptarlo como un ejemplo de éxito).
                                    </p>
                                    <hr class="d-none d-lg-block mb-0 ml-0" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center no-gutters">
                    <div class="col-lg-6 order-lg-2">
                        <img class="img-fluid" src="assets/img/project_infrastructure.jpg" alt="Plataforma de Monitoreo de Infraestructura Hídrica" />
                    </div>
                    <div class="col-lg-6 order-lg-1">
                        <div class="bg-white text-center h-100 project">
                            <div class="d-flex h-100">
                                <div class="project-text w-100 my-auto text-center text-lg-right">
                                    <h4 class="text-black">Plataforma de Monitoreo de Infraestructura Hídrica</h4>
                                    <p class="mb-0 text-black-50">
                                        Creación de un sistema GIS interactivo para la visualización y gestión de redes de acueducto y alcantarillado, facilitando la identificación de puntos críticos y la planificación de mantenimiento.
                                    </p>
                                    <hr class="d-none d-lg-block mb-0 mr-0" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
        </section>

        <section class="contact-section bg-black" id="contact">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-map-marked-alt text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Dirección</h4>
                                <hr class="my-4" />
                                <div class="small text-black-50">Cali, Valle del Cauca, Colombia</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Correo Electrónico</h4>
                                <hr class="my-4" />
                                <div class="small text-black-50"><a href="mailto:castrokof@gmail.com">castrokof@gmail.com</a></div>
                                <div class="small text-black-50"><a href="mailto:castrokof@gmail.com">castrokof@gmail.com</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="card py-4 h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-mobile-alt text-primary mb-2"></i>
                                <h4 class="text-uppercase m-0">Teléfono & WhatsApp <i class="fab fa-whatsapp-square" style='font-size:18px;color:green'></i></h4>
                                <hr class="my-4" />
                                <div class="small text-black-50">+57 317 5018125</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="social d-flex justify-content-center">
                    <a class="mx-2" href="#!"><i class="fab fa-twitter"></i></a>
                    <a class="mx-2" href="#!"><i class="fab fa-facebook-f"></i></a>
                    </div>
            </div>
        </section>

        <footer class="footer bg-black small text-center text-white-50">
            <div class="container">
                <strong>Copyright &copy; 2019-{{ date('Y') }} <a href="#page-top">Aqua Programmer<i class="fa fa-tint"></i></a>.</strong> Todos los derechos reservados.
            </div>
        </footer>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
        <script src="{{asset("assets/js/scriptsInicio.js")}}"></script>
    </body>
</html>
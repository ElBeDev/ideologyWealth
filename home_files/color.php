/* =============================================
   IDEOLOGY WEALTH ADVISORS — BLACK & GOLD THEME
   Color override file — ALL blues/greens removed
   ============================================= */

/* ---- GOLD accent elements ---- */
.custom--cl,
.service-card__icon i,
.choose-card__icon i,
.overview-card__icon i,
.ratings i,
.footer-info-list i,
.header .main-menu li a:hover,
.header .main-menu li a:focus,
.header .main-menu li a.active,
.header .main-menu li.menu_has_children:hover > a::before,
.header .main-menu li.menu_has_children > a::before,
.contact-info-card__icon i,
.plan-feature-list li::before,
.bottom-menu ul li a:hover,
.bottom-menu ul li a.active,
.dl__corner--top:before,
.dl__corner--top:after,
.dl__corner--bottom:before,
.dl__corner--bottom:after,
.account-short-link li a:hover,
.custom--checkbox input:checked ~ label::before,
.feature-card i,
.text--base,
.page-breadcrumb li a:hover,
.subscribe-form .form--control:focus ~ i,
.header .main-menu li .sub-menu li a:hover,
.page-breadcrumb li:first-child::before {
    color: #d4af37 !important;
    -webkit-text-fill-color: #d4af37;
}

/* ---- GOLD backgrounds ---- */
.btn--base,
.plan-card__header,
.bg--base,
.nav-tabs .nav-item.show .nav-link,
.nav-tabs .nav-link.active,
.dl__square,

.feature-card::after,
.section-top-title.border-left::before,
.about-thumb .video-icon,
.how-work-card__step::before,
.gradient--bg,
.btn--gradient::before,
.custom--accordion .accordion-button:not(.collapsed),
.btn-outline--gradient,
.pagination .page-item.active .page-link,
.custom--btn,
.btn-primary,
.how-work-card__step,
.overview-card__icon {
    background: #d4af37 !important;
    color: #111 !important;
    border-color: #d4af37 !important;
}

/* ---- DARK backgrounds (no more blue!) ---- */
.overview-section,
.testimonial-item,
.testimonial-item::before,
.footer,
.account-wrapper .left,
.btn--dark,
.btn--dark:hover,
.registration-wrapper .top-content,
.btn--base-2,
.custom--table thead,
.account-section-right,
.account-section-right::before,
.account-section-right::after,
.btn-outline--gradient::before,
.choose-card,
.dark--overlay::before,
.dark--overlay-two::before,
.plan-card .fdr-badge,
.beneficiary-card .nav-tabs .nav-link,
.service-section,
.loan-card,
.section--bg2,
.subscribe-section,
.account-form .form--control,
.account-form .select {
    background: #111111 !important;
    color: #f0ead6 !important;
    border-color: rgba(212, 175, 55, 0.15) !important;
}

/* Tables — negro, NO azul */
.custom--table,
.custom--table tbody tr {
    background: #161616 !important;
}

.custom--table thead {
    background: linear-gradient(135deg, #b8941f, #d4af37) !important;
}

.custom--table thead th {
    background: transparent !important;
    color: #111 !important;
}

.custom--table tbody td {
    color: #f0ead6 !important;
    border-bottom-color: rgba(255,255,255,0.05) !important;
}

/* Header — negro, NO blanco */
.header__bottom,
.header.menu-fixed .header__bottom,
.header__bottom2 {
    background-color: #080808 !important;
}

@media (max-width: 991px) {
    .header__bottom {
        background-color: #0d0d0d !important;
    }
}

/* Navbar sub-menu */
.header .main-menu li .sub-menu {
    background: #111 !important;
    border: 1px solid rgba(212,175,55,0.15) !important;
}

.header .main-menu li .sub-menu li a {
    color: #d4d4d4 !important;
}

.header .main-menu li .sub-menu li a:hover {
    color: #d4af37 !important;
    background: rgba(212,175,55,0.07) !important;
}

.header .main-menu li .sub-menu li a::before {
    color: #d4af37 !important;
}

/* Accordion */
.custom--accordion .accordion-item {
    border: 1px solid rgba(212,175,55,0.2) !important;
    background: #161616 !important;
}

.custom--accordion .accordion-button {
    background-color: rgba(212,175,55,0.05) !important;
    color: #f0ead6 !important;
}

/* Borders */
.form--control:focus,
.border--base,
.nav-tabs .nav-item.show .nav-link,
.nav-tabs .nav-link.active,
.expired-time-circle::before {
    border-color: #d4af37 !important;
}

/* Focus shadow */
.page-link:focus {
    box-shadow: 0 0 0 0.25rem rgba(212,175,55,0.3) !important;
}

/* Btn primary states */
.btn-primary:hover,
.btn-check:focus + .btn-primary,
.btn-primary:focus {
    background: #e8c547 !important;
    border-color: #e8c547 !important;
    color: #111 !important;
}

.btn:hover { color: #111 !important; }

/* Pagination links */
.pagination .page-item .page-link {
    border: 1px solid rgba(212,175,55,0.25) !important;
    background: #161616 !important;
    color: #d4af37 !important;
}

.pagination .page-item .page-link:hover,
.header .main-menu li .sub-menu li a::before {
    background-color: #d4af37 !important;
}

/* Plan card */
.plan-card {
    border-bottom: 3px solid #d4af37 !important;
    background: #161616 !important;
}

/* Service card icon bg */
.service-card__icon {
    background-color: rgba(212,175,55,0.1) !important;
}

.feature-card .icon {
    background-color: rgba(212,175,55,0.1) !important;
}

/* Overview icon gradient — gold */
.overview-card__icon i {
    background: linear-gradient(-103deg, #b8941f, #d4af37, #e8c547);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Light section bg — subtle dark */
.section--bg {
    background-color: rgba(212,175,55,0.04) !important;
}

/* Overview section after */
.overview-section::after {
    background: rgba(212,175,55,0.03) !important;
}

/* Service section override */
.service-section {
    background: #0d0d0d !important;
}

/* Choose card text */
.choose-card,
.choose-card * {
    color: #f0ead6 !important;
}

.choose-card h1,
.choose-card h2,
.choose-card h3,
.choose-card h4,
.choose-card h5,
.choose-card h6 {
    color: #ffffff !important;
}


/* ---- HEADER FIX — eliminar azul marino del navbar ---- */
.header__bottom,
.header__bottom2,
.header .header__bottom,
header.menu-fixed .header__bottom,
.header.menu-fixed .header__bottom,
.header.animated .header__bottom,
.header.animated.fadeInDown .header__bottom {
    background: #0a0a0a !important;
    background-color: #0a0a0a !important;
    border-bottom: 1px solid rgba(212,175,55,0.12) !important;
}


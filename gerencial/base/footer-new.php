<style>
.site-footer {
    background: linear-gradient(to bottom, #ffffff, #f1f5f9);
    border-top: 1px solid #e2e8f0;
    padding: 35px 30px 20px 30px;
    width: 100%;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 30px;
    flex-wrap: wrap;
}

.footer-brand strong {
    font-size: 16px;
    color: #334155;
}

.footer-brand p {
    font-size: 14px;
    color: #64748b;
    margin-top: 6px;
}

.footer-contact p {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 8px;
}

.footer-contact a {
    color: #f97316;
    text-decoration: none;
    font-weight: 600;
    transition: 0.2s ease;
}

.footer-contact a:hover {
    color: #ea580c;
    text-decoration: underline;
}

.footer-copy {
    width: 100%;
    text-align: center;
    font-size: 13px;
    color: #94a3b8;
    margin-top: 25px;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

/* MOBILE */
@media (max-width: 768px) {
    .site-footer {
        margin-left: 0;
        padding: 25px 20px;
        text-align: center;
    }

    .footer-content {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<footer class="site-footer">
    <div class="footer-content">

        <div class="footer-brand">
            <strong>Autodoc Gerencial</strong>
            <p>Todos os direitos reservados.</p>
        </div>

        <div class="footer-contact">
            <p>
                📧 <a href="mailto:thiagoallgayer.direito@gmail.com">
                    thiagoallgayer.direito@gmail.com
                </a>
            </p>

            <p>
                📱 <a href="https://wa.me/555195888759" target="_blank">
                    WhatsApp (51) 9588-8759
                </a>
            </p>
        </div>

    </div>
</footer>
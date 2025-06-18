<?php include 'header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4 text-center"><strong>Contact <span class="text-warning">Us</span></strong></h2>

    <div class="row g-5">
        <!-- Contact Form -->
        <div class="col-md-7">
            <form action="send_message.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Your Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Your Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea name="message" id="message" class="form-control" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn btn-warning text-white">Send Message</button>
            </form>
        </div>

        <!-- Contact Info -->
        <div class="col-md-5">
            <h5><i class="bi bi-geo-alt-fill text-warning me-2"></i> Our Address</h5>
            <p>Jl. Teknologi No.123, Kelurahan Mekarjaya, Kec. Sukmajaya, Depok, Jawa Barat 16411, Indonesia</p>

            <h5><i class="bi bi-envelope-fill text-warning me-2"></i> Email</h5>
            <p>support@aura.com</p>

            <h5><i class="bi bi-telephone-fill text-warning me-2"></i> Phone</h5>
            <p>+62 8958-10210-900</p>

            <h5><i class="bi bi-clock-fill text-warning me-2"></i> Working Hours</h5>
            <p>Mon - Fri: 9:00 AM - 6:00 PM</p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
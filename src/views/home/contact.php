<div class="container page-wrapper py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg p-5">
                <h1 class="h2 mb-2">Contact Us</h1>
                <p class="text-muted mb-4">Have questions about our processing queues or tenancy features? Drop us a line.</p>
                
                <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Message successfully processed via operational dispatch channels!');">
                    <div class="form-group mb-3">
                        <label class="form-label text-uppercase">Your Name</label>
                        <input type="text" class="form-control" placeholder="e.g. Alex Mercer" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label text-uppercase">Email Address</label>
                        <input type="email" class="form-control" placeholder="alex@company.com" required>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label class="form-label text-uppercase">Message Context</label>
                        <textarea class="form-control" rows="5" placeholder="Describe your operational inquiry..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Dispatch Inquiry Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
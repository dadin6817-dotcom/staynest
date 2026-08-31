<?php
// includes/footer.php
?>
<footer class="bg-gray-900 text-white py-8 mt-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-8">
            <div>
                <h3 class="text-xl font-bold gradient-text mb-4">StayNest</h3>
                <p class="text-gray-400 text-sm">Find your cozy home today.</p>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Quick Links</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="/staynest/index.php" class="hover:text-white transition">Home</a></li>
                    <li><a href="/staynest/properties.php" class="hover:text-white transition">Properties</a></li>
                    <li><a href="/staynest/bookings/my_bookings.php" class="hover:text-white transition">My Bookings</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Support</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                    <li><a href="#" class="hover:text-white transition">Contact</a></li>
                    <li><a href="#" class="hover:text-white transition">Terms</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Follow Us</h4>
                <div class="flex gap-4">
                    <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-6 text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> StayNest. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>
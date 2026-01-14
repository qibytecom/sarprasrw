<?php
/**
 * JavaScript Test Page
 * Halaman untuk testing JavaScript functionality
 */

session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test JavaScript - SARPRAS RW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-flask me-2"></i>Test JavaScript Functionality</h4>
                    </div>
                    <div class="card-body">
                        <h5>Test Results:</h5>
                        <div id="test-results" class="mt-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Testing JavaScript functionality...
                            </div>
                        </div>

                        <div class="mt-4">
                            <button id="test-toast" class="btn btn-primary me-2">
                                <i class="fas fa-bell me-2"></i>Test Toast
                            </button>
                            <button id="test-modal" class="btn btn-success me-2">
                                <i class="fas fa-window-maximize me-2"></i>Test Modal
                            </button>
                            <button id="test-ajax" class="btn btn-warning">
                                <i class="fas fa-exchange-alt me-2"></i>Test AJAX
                            </button>
                        </div>

                        <div class="mt-4">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-home me-2"></i>Kembali ke Home
                            </a>
                            <a href="auth/login.php" class="btn btn-info ms-2">
                                <i class="fas fa-sign-in-alt me-2"></i>Ke Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    <!-- Test Modal -->
    <div class="modal fade" id="testModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Modal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Modal berhasil ditampilkan! JavaScript Bootstrap berfungsi dengan baik.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>

    <script>
        // Test JavaScript functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ DOM Content Loaded');

            var resultsDiv = document.getElementById('test-results');

            // Test 1: Basic JavaScript
            try {
                console.log('✅ Basic JavaScript OK');
                resultsDiv.innerHTML += '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Basic JavaScript: OK</div>';
            } catch (e) {
                console.error('❌ Basic JavaScript Error:', e);
                resultsDiv.innerHTML += '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Basic JavaScript: Error - ' + e.message + '</div>';
            }

            // Test 2: Bootstrap JS
            try {
                if (typeof bootstrap !== 'undefined') {
                    console.log('✅ Bootstrap JS OK');
                    resultsDiv.innerHTML += '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Bootstrap JS: OK</div>';
                } else {
                    throw new Error('Bootstrap not loaded');
                }
            } catch (e) {
                console.error('❌ Bootstrap JS Error:', e);
                resultsDiv.innerHTML += '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Bootstrap JS: Error - ' + e.message + '</div>';
            }

            // Test 3: Custom Script
            try {
                if (typeof showToast !== 'undefined') {
                    console.log('✅ Custom Script OK');
                    resultsDiv.innerHTML += '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Custom Script: OK</div>';
                } else {
                    throw new Error('Custom script not loaded');
                }
            } catch (e) {
                console.error('❌ Custom Script Error:', e);
                resultsDiv.innerHTML += '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Custom Script: Error - ' + e.message + '</div>';
            }

            // Test Toast Button
            document.getElementById('test-toast').addEventListener('click', function() {
                try {
                    showToast('Toast test berhasil!', 'success');
                    console.log('✅ Toast Test OK');
                } catch (e) {
                    console.error('❌ Toast Test Error:', e);
                    alert('Toast Error: ' + e.message);
                }
            });

            // Test Modal Button
            document.getElementById('test-modal').addEventListener('click', function() {
                try {
                    var modal = new bootstrap.Modal(document.getElementById('testModal'));
                    modal.show();
                    console.log('✅ Modal Test OK');
                } catch (e) {
                    console.error('❌ Modal Test Error:', e);
                    alert('Modal Error: ' + e.message);
                }
            });

            // Test AJAX Button
            document.getElementById('test-ajax').addEventListener('click', function() {
                try {
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', 'test_db.php', true);
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            showToast('AJAX test berhasil!', 'success');
                            console.log('✅ AJAX Test OK');
                        } else {
                            throw new Error('AJAX failed with status ' + xhr.status);
                        }
                    };
                    xhr.onerror = function() {
                        throw new Error('AJAX network error');
                    };
                    xhr.send();
                } catch (e) {
                    console.error('❌ AJAX Test Error:', e);
                    alert('AJAX Error: ' + e.message);
                }
            });

            // Overall status
            setTimeout(function() {
                var errorAlerts = resultsDiv.querySelectorAll('.alert-danger');
                if (errorAlerts.length === 0) {
                    resultsDiv.innerHTML += '<div class="alert alert-success mt-3"><i class="fas fa-thumbs-up me-2"></i><strong>Semua test berhasil!</strong> JavaScript berfungsi dengan baik.</div>';
                } else {
                    resultsDiv.innerHTML += '<div class="alert alert-warning mt-3"><i class="fas fa-exclamation-triangle me-2"></i><strong>Ada ' + errorAlerts.length + ' error(s) ditemukan.</strong> Periksa console browser (F12) untuk detail.</div>';
                }
            }, 1000);
        });
    </script>
</body>
</html>

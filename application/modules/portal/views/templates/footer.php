                            </div>
                        </div>
					</div>
					<div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
						<div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between">
							<div class="text-dark order-2 order-md-1">
								<span class="text-muted fw-bold me-1"><?=date('Y') ?> ©</span>
								<a href="<?=base_url() ?>portal/dasboard" target="_blank" class="text-gray-800 text-hover-primary">GC & C</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
        
		<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
			<span class="svg-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="black" />
					<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="black" />
				</svg>
			</span>
		</div>

		<script src="<?=base_url('assets/js/vue.min.js') ?>"></script>
		<script type="text/javascript" src="<?=base_url('assets/js/bootstrap/bootstrap.min.js') ?>"></script>
        <script type="text/javascript" src="<?=base_url('assets/plugins/global/plugins.bundle.js') ?>"></script>
        <script type="text/javascript" src="<?=base_url('assets/js/scripts.bundle.js') ?>"></script>
		<script type="text/javascript" src="<?=base_url('assets/js/fullcalendar/index.global.js') ?>"></script>
        <script type="text/javascript" src="<?=base_url('assets/js/app/custom.js') ?>"></script>
        <script type="text/javascript" src="<?=base_url('assets/js/datatables/datatables.bundle.js') ?>"></script>
		<script type="text/javascript" src="<?=base_url('assets/js/jscolor.js') ?>"></script>
		<script type="text/javascript" src="<?=base_url('assets/js/custom/moment.min.js') ?>"></script>
		<?=$this->core->getStoredJs() ?>
    </body>
</html>
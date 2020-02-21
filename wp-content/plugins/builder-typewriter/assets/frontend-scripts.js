(function ( $, window ) {
	"use strict";
	var isWorking = true,
		BuilderTypewriter = {
			init: function () {
				var self = this;

				function wload() {
					if ( window.loaded ) {
						self.typeWriter();
					} else {
						$( window ).one('load', function () {
							self.typeWriter();
						} );
					}
				}

				if ( Themify.is_builder_active ) {
					if ( Themify.is_builder_loaded ) {
						wload();
					} else {
						isWorking = null;
					}
				} else {
					wload();
				}
				Themify.body.on( 'builder_load_module_partial', function ( e, el, type ) {
					if ( isWorking === null ) {
						self.typeWriter( el );
					}
				} );
			},
			typeWriter: function ( el ) {
				var $typewriter = $( '[data-typer-targets]', el );
				if ( el && el.data( 'typer-targets' ) ) {
					$typewriter = $typewriter.add( el );
				}
				var callback = function () {
					$typewriter.each( function () {
						var $this = $( this );
						if ( Themify.is_builder_active ) {
                                                    $this.innerText = '';
                                                    if ( typeof tb_app!=='undefined' && tb_app.activeModel !== null ) {
                                                        tb_app.liveStylingInstance.setLiveStyle('background-color', ThemifyConstructor.getStyleVal('span_background_color'), ThemifyStyles.getStyleOptions('typewriter').span_background_color.selector);
                                                    }
						}
						$this.typer( {
							highlightSpeed: parseInt( $this.data( 'typer-highlight-speed' ) ),
							typeSpeed: parseInt( $this.data( 'typer-type-speed' ) ),
							clearDelay: parseInt( parseFloat( $this.data( 'typer-clear-delay' ) ) * 1000 ),
							typeDelay: parseInt( parseFloat( $this.data( 'typer-type-delay' ) ) * 1000 ),
							clearOnHighlight: true,
							typerDataAttr: 'data-typer-targets',
							typerInterval: parseInt( parseFloat( $this.data( 'typer-interval' ) ) * 1000 ),
							typerOrder: 'sequential',
							typerDirection: $this.data( 'typer-direction' ),
							typerStartFrom: Themify.is_builder_active ? 0 : 1,
							inlineHighlightStyle: false,
						} );

					} );
					isWorking = null;
				};
				if ( $typewriter.length > 0 ) {
					if ( 'undefined' !== typeof $.fn.typer ) {
						callback();
					} else {
						Themify.LoadAsync(
							tb_typewriter_vars.url + 'assets/jquery.typer.themify.js',
							callback,
							null,
							null,
							function () {
								return ('undefined' !== typeof $.fn.typer);
							}
						);
					}
				} else {
					isWorking = null;
				}
			}
		};

	BuilderTypewriter.init();
}( jQuery, window ));

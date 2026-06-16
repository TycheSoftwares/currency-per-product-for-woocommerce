/**
 * WordPress dependencies.
 */
import {
	Button,
	Notice,
	__experimentalConfirmDialog as ConfirmDialog,
	__experimentalHStack as HStack,
} from '@wordpress/components';
import { useRef, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Sticky Save/Reset actions bar.
 *
 * Stays inline at the bottom of the form. When the inline position scrolls
 * out of view (in either direction), a fixed bar appears at the viewport bottom.
 *
 * @param {Object}   props
 * @param {boolean}  props.isSaving       Whether save is in progress.
 * @param {Object}   props.notice         Notice to display: { message, status }.
 * @param {Function} props.onNoticeRemove Called when the notice is dismissed.
 * @param {Function} props.onReset        Called when Reset Settings is clicked.
 */
const FormActions = ( { isSaving, notice, onNoticeRemove, onReset } ) => {
	const sentinelRef = useRef( null );
	const [ isSticky,      setIsSticky      ] = useState( false );
	const [ isDialogOpen,  setIsDialogOpen  ] = useState( false );

	useEffect( () => {
		const sentinel = sentinelRef.current;
		if ( ! sentinel || ! window.IntersectionObserver ) return;

		const observer = new IntersectionObserver(
			( [ entry ] ) => setIsSticky( ! entry.isIntersecting ),
			{ threshold: 0 }
		);
		observer.observe( sentinel );
		return () => observer.disconnect();
	}, [] );

	return (
		<>
			{ /* Sentinel: stays in flow so we can detect when the bar scrolls off-screen */ }
			<div ref={ sentinelRef } style={ { height: 1, marginBottom: -1 } } aria-hidden />

			<div className={ `cpp-general-actions${ isSticky ? ' is-sticky' : '' }` }>
				<HStack spacing={ 3 } expanded={ false } justify="left" alignment="center">
					<Button
						variant="primary"
						type="submit"
						isBusy={ isSaving }
						disabled={ isSaving }
					>
						{ __( 'Save Changes', 'currency-per-product-for-woocommerce' ) }
					</Button>
					<Button
						variant="secondary"
						type="button"
						onClick={ () => setIsDialogOpen( true ) }
						disabled={ isSaving }
					>
						{ __( 'Reset Settings', 'currency-per-product-for-woocommerce' ) }
					</Button>
					{ notice?.message && (
						<Notice
							status={ notice.status ?? 'success' }
							onRemove={ onNoticeRemove }
							isDismissible={ true }
						>
							{ notice.message }
						</Notice>
					) }
				</HStack>
				<ConfirmDialog
					isOpen={ isDialogOpen }
					cancelButtonText={ __( 'Cancel', 'currency-per-product-for-woocommerce' ) }
					confirmButtonText={ __( 'Reset', 'currency-per-product-for-woocommerce' ) }
					onCancel={ () => setIsDialogOpen( false ) }
					onConfirm={ () => { setIsDialogOpen( false ); onReset?.(); } }
				>
					{ __( 'Are you sure you want to reset settings from this section to their default values? This cannot be undone.', 'currency-per-product-for-woocommerce' ) }
				</ConfirmDialog>
			</div>
		</>
	);
};

export default FormActions;

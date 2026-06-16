/**
 * WordPress dependencies.
 */
import {
	__experimentalVStack as VStack,
	__experimentalHStack as HStack,
	__experimentalHeading as Heading,
	__experimentalText as Text,
	Icon,
} from '@wordpress/components';

/**
 * External dependencies.
 */
import { NavLink } from 'react-router-dom';

/**
 * Header component — plugin title, description, and tab navigation.
 *
 * @param {Object} props
 * @param {string} props.title       Plugin title.
 * @param {string} props.description Short description shown below the title.
 * @param {Array}  props.tabs        Array of { name, title, path, icon } objects.
 */
const Header = ( { title, description, tabs } ) => {
	return (
		<div className="cpp-plugin-header">
			<VStack spacing={ 1 } className="cpp-plugin-header__title-wrap">
				<Heading level={ 1 } className="cpp-plugin-header__title">
					{ title }
				</Heading>
				{ description && (
					<Text className="cpp-plugin-header__description">
						{ description }
					</Text>
				) }
			</VStack>

			<HStack className="cpp-plugin-header__tabs-row">
				<div className="cpp-header-dashboard-tabs">
					{ tabs.map( ( tab ) => (
						<NavLink
							key={ tab.name }
							to={ tab.path }
							className={ ( { isActive } ) =>
								'cpp-dashboard-tab' + ( isActive ? ' is-active' : '' )
							}
						>
							{ tab.icon && (
								// <Icon
								// 	icon={ tab.icon }
								// 	size={ 14 }
								// 	className="cpp-dashboard-tab__icon"
								// />
								<span className={`dashicons ${tab.icon}`} aria-hidden="true" />
							) }
							{ tab.title }
						</NavLink>
					) ) }
				</div>
			</HStack>
		</div>
	);
};

export default Header;

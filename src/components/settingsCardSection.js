import {
	Card,
	CardHeader,
	CardBody,
	__experimentalVStack as VStack,
	__experimentalHeading as Heading,
	__experimentalText as Text,
} from '@wordpress/components';
import { Controller } from 'react-hook-form';

const SettingsCardSection = ( {
	heading,
	subHeading = null,
	fields = [],
	display = true,
	control,
	className = '',
} ) => {
	if ( ! display ) {
		return null;
	}

	return (
		<Card className={ className }>
			<CardHeader>
				<VStack spacing={ 2 }>
					<Heading level={ 4 }>{ heading }</Heading>
					{ subHeading && (
						<Text className="components-text">{ subHeading }</Text>
					) }
				</VStack>
			</CardHeader>

			<CardBody>
				<VStack spacing={ 6 }>
					{ fields.map( ( field, index ) =>
						field.showWhen === undefined || field.showWhen ? (
							<div key={ field.name ?? index } className="cpp-settings-row">
								{ field.label && (
									<div className="cpp-settings-row__label">
										<Text className="cpp-settings-label">{ field.label }</Text>
									</div>
								) }
								<div className="cpp-settings-row__control">
									<Controller
										name={ field.name }
										control={ control }
										defaultValue={ field.defaultValue }
										rules={ field.rules }
										render={ ( { field: controllerField, fieldState: { error } } ) =>
											field.render( controllerField, error )
										}
									/>
								</div>
							</div>
						) : null
					) }
				</VStack>
			</CardBody>
		</Card>
	);
};

export default SettingsCardSection;

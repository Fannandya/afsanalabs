export interface FieldOption {
	value: string;
	label: string;
}

export interface FieldDef {
	key: string;
	label: string;
	type: 'text' | 'textarea' | 'number' | 'checkbox' | 'select' | 'image' | 'json';
	options?: FieldOption[];
	required?: boolean;
	folder?: string;
}

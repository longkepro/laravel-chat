import { InputHTMLAttributes, forwardRef } from 'react';

function mergeClasses(base: string, extra?: string) {
    return extra ? `${base} ${extra}` : base;
}

export type FormInputProps = InputHTMLAttributes<HTMLInputElement>;

const FormInput = forwardRef<HTMLInputElement, FormInputProps>(({ className, ...rest }, ref) => (
    <input
        ref={ref}
        {...rest}
        className={mergeClasses(
            'w-full h-10 px-3 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500',
            className,
        )}
    />
));

FormInput.displayName = 'FormInput';

export default FormInput;

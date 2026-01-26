import { ButtonHTMLAttributes, PropsWithChildren } from 'react';

function mergeClasses(base: string, extra?: string) {
    return extra ? `${base} ${extra}` : base;
}

export type FormButtonProps = PropsWithChildren<ButtonHTMLAttributes<HTMLButtonElement>>;

export function FormButton({ children, className, ...rest }: FormButtonProps) {
    return (
        <button
            {...rest}
            className={mergeClasses(
                'w-4/5 h-11 bg-green-600 text-white rounded hover:bg-green-700 cursor-pointer transition',
                className,
            )}
        >
            {children}
        </button>
    );
}

export default FormButton;

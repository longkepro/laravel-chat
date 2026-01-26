import { HTMLAttributes, PropsWithChildren } from 'react';

function mergeClasses(base: string, extra?: string) {
    return extra ? `${base} ${extra}` : base;
}

export type FormTitleProps = PropsWithChildren<HTMLAttributes<HTMLHeadingElement>>;

export function FormTitle({ children, className, ...rest }: FormTitleProps) {
    return (
        <h1 {...rest} className={mergeClasses('text-3xl font-bold text-green-700 mb-6', className)}>
            {children}
        </h1>
    );
}

export default FormTitle;

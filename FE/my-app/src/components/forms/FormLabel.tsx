import { LabelHTMLAttributes, PropsWithChildren } from 'react';

function mergeClasses(base: string, extra?: string) {
    return extra ? `${base} ${extra}` : base;
}

export type FormLabelProps = PropsWithChildren<LabelHTMLAttributes<HTMLLabelElement>>;

export function FormLabel({ children, className, ...rest }: FormLabelProps) {
    return (
        <label {...rest} className={mergeClasses('block text-sm font-medium text-slate-700 mb-2', className)}>
            {children}
        </label>
    );
}

export default FormLabel;

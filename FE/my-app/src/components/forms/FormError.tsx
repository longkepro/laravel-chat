export type FormErrorProps = {
    message?: string | null;
    className?: string;
};

function mergeClasses(base: string, extra?: string) {
    return extra ? `${base} ${extra}` : base;
}

export function FormError({ message, className }: FormErrorProps) {
    if (!message) return null;

    return <p className={mergeClasses('text-xs text-red-500 font-semibold mt-1', className)}>{message}</p>;
}

export default FormError;

import { InputHTMLAttributes } from 'react';

function mergeClasses(base: string, extra?: string) {
    return extra ? `${base} ${extra}` : base;
}

export type SearchInputProps = InputHTMLAttributes<HTMLInputElement> & {
    placeholder?: string;
};

export function SearchInput({ className, placeholder = 'Search...', ...rest }: SearchInputProps) {
    return (
        <div className="border-b-2 py-4 px-2">
            <input
                type="text"
                placeholder={placeholder}
                {...rest}
                className={mergeClasses('py-2 px-2 border-2 border-gray-200 rounded-2xl w-full', className)}
            />
        </div>
    );
}

export default SearchInput;

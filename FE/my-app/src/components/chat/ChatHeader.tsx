import { InputHTMLAttributes } from 'react';

function mergeClasses(base: string, extra?: string) {
    return extra ? `${base} ${extra}` : base;
}

export type ChatHeaderProps = {
    title?: string;
    searchPlaceholder?: string;
    searchValue?: string;
    onSearchChange?: InputHTMLAttributes<HTMLInputElement>['onChange'];
    avatarText?: string;
    className?: string;
};

export function ChatHeader({
    title = 'GoingChat',
    searchPlaceholder = 'search IRL',
    searchValue,
    onSearchChange,
    avatarText = 'RA',
    className,
}: ChatHeaderProps) {
    return (
        <div className={mergeClasses('px-5 py-5 flex justify-between items-center bg-white border-b-2', className)}>
            <div className="font-semibold text-2xl">{title}</div>
            <div className="w-1/2">
                <input
                    type="text"
                    placeholder={searchPlaceholder}
                    value={searchValue}
                    onChange={onSearchChange}
                    className="rounded-2xl bg-gray-100 py-3 px-5 w-full"
                />
            </div>
            <div className="h-12 w-12 p-2 bg-yellow-500 rounded-full text-white font-semibold flex items-center justify-center">
                {avatarText}
            </div>
        </div>
    );
}

export default ChatHeader;

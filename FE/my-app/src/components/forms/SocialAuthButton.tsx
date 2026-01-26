import { AnchorHTMLAttributes, PropsWithChildren } from 'react';

function mergeClasses(base: string, extra?: string) {
    return extra ? `${base} ${extra}` : base;
}

export type SocialAuthButtonProps = PropsWithChildren<
    AnchorHTMLAttributes<HTMLAnchorElement> & {
        url: string;
        logo: string;
    }
>;

export function SocialAuthButton({ url, logo, children, className, ...rest }: SocialAuthButtonProps) {
    return (
        <a
            href={url}
            {...rest}
            className={mergeClasses('flex items-center px-4 py-2 rounded-lg shadow-sm bg-white hover:bg-gray-100', className)}
        >
            <img src={logo} alt="Logo" className="h-5 w-5 mr-3" />
            {children}
        </a>
    );
}

export default SocialAuthButton;

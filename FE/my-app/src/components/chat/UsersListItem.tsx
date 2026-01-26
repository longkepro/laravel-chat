import { HTMLAttributes } from 'react';

function mergeClasses(base: string, extra?: string) {
    return extra ? `${base} ${extra}` : base;
}

export type ChatUser = {
    id: string | number;
    avatar?: string | null;
    username: string;
    lastMessage?: { message?: string | null } | null;
    unread?: boolean;
};

export type UsersListItemProps = HTMLAttributes<HTMLDivElement> & {
    user: ChatUser;
    onSelect?: (user: ChatUser) => void;
};

export function UsersListItem({ user, className, onSelect, ...rest }: UsersListItemProps) {
    const handleClick = () => {
        onSelect?.(user);
    };

    return (
        <div
            {...rest}
            onClick={handleClick}
            data-user-id={user.id}
            className={mergeClasses(
                'user-card flex flex-row py-4 px-2 justify-center items-center border-b-2 hover:bg-gray-100 cursor-pointer',
                className,
            )}
        >
            <div className="w-1/4">
                <img
                    src={user.avatar || 'https://source.unsplash.com/_7LbC5J-jw4/600x600'}
                    className="flex flex-row py-4 px-2 justify-center items-center border-b-2"
                    alt={user.username}
                />
            </div>

            <div className="w-full flex justify-between items-center">
                <div>
                    <div className="text-lg font-semibold">{user.username}</div>
                    <span className={user.unread ? 'font-bold text-black' : 'text-gray-500'}>
                        {user.lastMessage?.message || 'No messages yet'}
                    </span>
                </div>

                {user.unread ? <span className="inline-block w-3 h-3 bg-green-500 rounded-full" /> : null}
            </div>
        </div>
    );
}

export default UsersListItem;

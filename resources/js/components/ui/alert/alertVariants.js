import { cva } from 'class-variance-authority';

export const alertVariants = cva(
    'relative w-full rounded-lg border p-sp-sm text-body-md [&>svg]:absolute [&>svg]:left-sp-sm [&>svg]:top-sp-sm [&>svg]:h-5 [&>svg]:w-5 [&>svg~*]:pl-7',
    {
        variants: {
            variant: {
                default: 'border-outline-variant bg-surface-container-lowest text-on-surface',
                destructive: 'border-error/30 bg-error-container text-on-error-container [&>svg]:text-error',
                success: 'border-[#0a5a26]/25 bg-[#d7f5dd] text-[#0a5a26] [&>svg]:text-[#0a5a26]',
                warning: 'border-primary-container/25 bg-secondary-container text-primary-container [&>svg]:text-primary-container',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    },
);

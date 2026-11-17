import { cva } from 'class-variance-authority';

export const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded uppercase transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
    {
        variants: {
            variant: {
                default: 'bg-secondary-container text-primary-container hover:bg-secondary-fixed-dim',
                secondary: 'bg-primary-container text-on-primary hover:bg-on-primary-fixed-variant',
                outline: 'border-2 border-primary-container text-primary-container bg-transparent hover:bg-primary-container hover:text-on-primary',
                ghost: 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface',
                destructive: 'bg-error text-on-error hover:bg-on-error-container',
                link: 'text-primary-container underline-offset-4 hover:underline normal-case font-normal',
            },
            size: {
                default: 'h-12 px-sp-md text-button',
                sm: 'h-9 px-sp-sm font-sans text-xs font-bold tracking-wide',
                lg: 'h-14 px-sp-lg text-button',
                icon: 'h-9 w-9 p-0 text-button',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

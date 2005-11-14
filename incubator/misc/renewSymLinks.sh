cd core/.all && for n in `find .. -name '*.php'`; do ln -s $n `basename $n`; done
cd -
cd main/.all && for n in `find .. -name '*.php'`; do ln -s $n `basename $n`; done
cd -
